<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
require_once __DIR__ . '/../classes/PaymentGateway.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /checkout.php");
    exit;
}

Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: /login.php");
    exit;
}

$address_id = filter_input(INPUT_POST, 'address_id', FILTER_VALIDATE_INT);
$payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
$cart_id = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
$total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);

if (!$address_id || !$payment_method || !$cart_id || !$total_amount) {
    die("Invalid checkout parameters.");
}

try {
    // Security: Verify that cart and address belong to the logged-in user
    $stmtVerifyCart = $pdo->prepare("SELECT id FROM carts WHERE id = ? AND user_id = ?");
    $stmtVerifyCart->execute([$cart_id, $user_id]);
    if (!$stmtVerifyCart->fetch()) {
        die("Unauthorized cart access.");
    }

    $stmtVerifyAddress = $pdo->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmtVerifyAddress->execute([$address_id, $user_id]);
    if (!$stmtVerifyAddress->fetch()) {
        die("Unauthorized address access.");
    }

    $pdo->beginTransaction();

    // 1. Fetch Cart Items
    $stmt = $pdo->prepare("
        SELECT ci.variant_id, ci.quantity, p.base_price, v.additional_price
        FROM cart_items ci
        JOIN product_variants v ON ci.variant_id = v.id
        JOIN products p ON v.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart_id]);
    $cartItems = $stmt->fetchAll();

    if (empty($cartItems)) {
        throw new Exception("Cart is empty.");
    }

    // 2. Verify Stock (Simplified)
    foreach ($cartItems as $item) {
        $stmtStock = $pdo->prepare("SELECT stock_quantity FROM product_inventory WHERE variant_id = ? FOR UPDATE");
        $stmtStock->execute([$item['variant_id']]);
        $stock = $stmtStock->fetchColumn();

        if ($stock < $item['quantity']) {
            throw new Exception("Insufficient stock for one or more items.");
        }
    }

    // 3. Apply Pending Redemption (if any)
    $redemptionValue = 0;
    $redemptionGrams = 0;
    $enrollment_id = null;

    if (isset($_SESSION['pending_redemption'])) {
        $enrollment_id = $_SESSION['pending_redemption']['enrollment_id'];
        $redemptionGrams = $_SESSION['pending_redemption']['gold_grams'];

        $today = date('Y-m-d');
        $stmtRate = $pdo->prepare("SELECT rate_per_gram FROM scheme_gold_rates WHERE date = ? AND metal_type = 'gold' AND purity = '22K'");
        $stmtRate->execute([$today]);
        $rate = $stmtRate->fetchColumn() ?: 0;

        $redemptionValue = $redemptionGrams * $rate;
    }

    // 4. Create Order Record
    // Note: Re-calculating totals server-side for security
    $calculated_subtotal = 0;
    foreach ($cartItems as $item) {
        $calculated_subtotal += (($item['base_price'] + $item['additional_price']) * $item['quantity']);
    }

    if ($redemptionValue > $calculated_subtotal) {
        $redemptionValue = $calculated_subtotal;
    }

    $discount_amount = $redemptionValue; // Apply redemption as a discount
    $taxable_amount = $calculated_subtotal - $discount_amount;

    $gst = $taxable_amount * 0.03;
    $shipping = $taxable_amount < 50000 ? 500 : 0;
    $final_amount = $taxable_amount + $gst + $shipping;

    $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, address_id, total_amount, discount_amount, tax_amount, final_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmtOrder->execute([$user_id, $address_id, $calculated_subtotal, $discount_amount, $gst, $final_amount]);
    $order_id = $pdo->lastInsertId();

    // 5. Finalize Redemption (if applicable)
    if ($enrollment_id) {
        // Insert into redemption log
        $stmtRedeem = $pdo->prepare("INSERT INTO scheme_redemptions (enrollment_id, order_id, redeemed_gold_grams, value_in_currency, redemption_date) VALUES (?, ?, ?, ?, CURDATE())");
        $stmtRedeem->execute([$enrollment_id, $order_id, $redemptionGrams, $redemptionValue]);

        // Update enrollment status
        $stmtClose = $pdo->prepare("UPDATE scheme_enrollments SET status = 'redeemed' WHERE id = ?");
        $stmtClose->execute([$enrollment_id]);

        unset($_SESSION['pending_redemption']);
    }

    // 6. Insert Order Items & Deduct Inventory
    foreach ($cartItems as $item) {
        $price_per_unit = $item['base_price'] + $item['additional_price'];
        $line_total = $price_per_unit * $item['quantity'];

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, variant_id, quantity, price_per_unit, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmtItem->execute([$order_id, $item['variant_id'], $item['quantity'], $price_per_unit, $line_total]);

        $stmtDeduct = $pdo->prepare("UPDATE product_inventory SET stock_quantity = stock_quantity - ? WHERE variant_id = ?");
        $stmtDeduct->execute([$item['quantity'], $item['variant_id']]);
    }

    // 7. Clear Cart
    $stmtClear = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmtClear->execute([$cart_id]);

    // 8. Handle Payment Integration Logic
    if ($payment_method === 'cod') {
        // Direct to success
        $pdo->commit();
        header("Location: /user-dashboard/index.php?order_success=1&order_id=" . $order_id);
        exit;
    } else {
        // Initialize gateway for Razorpay/etc.
        $gateway = new PaymentGateway();
        $paymentData = $gateway->initializePayment($order_id, $final_amount, 'razorpay');

        // Record pending transaction
        $stmtTrans = $pdo->prepare("INSERT INTO transactions (order_id, transaction_id, payment_method, amount, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmtTrans->execute([$order_id, $paymentData['transaction_id'], $payment_method, $final_amount]);

        $pdo->commit();

        // In a real scenario, this would redirect to the payment gateway's hosted page or return token to frontend
        // We will simulate success
        header("Location: /user-dashboard/index.php?order_success=1&order_id=" . $order_id);
        exit;
    }

} catch (Exception $e) {
    $pdo->rollBack();
    die("Checkout failed: " . $e->getMessage());
}
