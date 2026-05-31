<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/assets/components/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Fetch Addresses
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

// Fetch Cart Data (simplified matching cart.php)
$cartItems = [];
$subtotal = 0;
$cart_id = null;

$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch();

if ($cart) {
    $cart_id = $cart['id'];
    $stmt = $pdo->prepare("
        SELECT ci.quantity, v.additional_price, p.base_price
        FROM cart_items ci
        JOIN product_variants v ON ci.variant_id = v.id
        JOIN products p ON v.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart_id]);
    $cartItems = $stmt->fetchAll();
}

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

foreach ($cartItems as $item) {
    $itemPrice = $item['base_price'] + $item['additional_price'];
    $subtotal += ($itemPrice * $item['quantity']);
}

$gst = $subtotal * 0.03;
$shipping = $subtotal < 50000 ? 500 : 0;
$total = $subtotal + $gst + $shipping;

// Check for pending redemption
$redemptionValue = 0;
$redemptionGrams = 0;
if (isset($_SESSION['pending_redemption'])) {
    $redemptionGrams = $_SESSION['pending_redemption']['gold_grams'];
    // Fetch today's rate to calculate redemption value
    $today = date('Y-m-d');
    $stmtRate = $pdo->prepare("SELECT rate_per_gram FROM scheme_gold_rates WHERE date = ? AND metal_type = 'gold' AND purity = '22K'");
    $stmtRate->execute([$today]);
    $rate = $stmtRate->fetchColumn() ?: 0;

    $redemptionValue = $redemptionGrams * $rate;
    // Cap redemption value at the order's subtotal to prevent negative orders
    if ($redemptionValue > $subtotal) {
        $redemptionValue = $subtotal;
    }

    // Recalculate total
    $total = ($subtotal - $redemptionValue) + $gst + $shipping;
}
?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="bg-brand-cultured min-h-screen pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-10 text-center reveal active">
            <h1 class="text-3xl md:text-5xl font-bold text-brand-wine mb-4">Checkout</h1>
            <p class="text-brand-wine/70 font-light">Securely complete your purchase.</p>
        </div>

        <form id="checkout-form" method="POST" action="api/checkout.php" class="flex flex-col lg:flex-row gap-10 reveal active">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="cart_id" value="<?php echo $cart_id; ?>">
            <input type="hidden" name="total_amount" value="<?php echo $total; ?>">

            <!-- Checkout Steps -->
            <div class="lg:w-2/3 space-y-8">

                <!-- Step 1: Address -->
                <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-brand-gold/20">
                    <h2 class="text-xl font-bold text-brand-wine mb-6 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-brand-wine text-white flex items-center justify-center text-sm mr-3">1</span>
                        Shipping Address
                    </h2>

                    <?php if (empty($addresses)): ?>
                        <div class="p-4 bg-brand-cultured/50 rounded-xl border border-brand-gold/30 mb-6 text-center">
                            <p class="text-brand-wine/70 text-sm mb-4">No saved addresses found.</p>
                            <button type="button" onclick="document.getElementById('new-address-form').classList.remove('hidden')" class="text-brand-gold text-sm font-medium hover:text-brand-wine">
                                + Add New Address
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <?php foreach ($addresses as $index => $address): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="address_id" value="<?php echo $address['id']; ?>" class="peer sr-only" <?php echo ($address['is_default'] || $index === 0) ? 'checked' : ''; ?> required>
                                    <div class="p-4 rounded-xl border border-brand-gold/30 hover:border-brand-wine peer-checked:border-brand-wine peer-checked:bg-brand-wine/5 transition-all h-full flex flex-col">
                                        <div class="font-medium text-brand-wine mb-1 flex justify-between">
                                            <span><?php echo htmlspecialchars($address['city']); ?> Address</span>
                                            <?php if ($address['is_default']): ?>
                                                <span class="text-[10px] bg-brand-gold/20 text-brand-gold px-2 py-0.5 rounded uppercase">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-brand-wine/70 font-light mt-2 line-clamp-2">
                                            <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                            <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['pincode']); ?>
                                        </p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="text-brand-gold text-sm font-medium hover:text-brand-wine">
                            + Add New Address
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Step 2: Payment Method -->
                <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-brand-gold/20">
                    <h2 class="text-xl font-bold text-brand-wine mb-6 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-brand-wine text-white flex items-center justify-center text-sm mr-3">2</span>
                        Payment Method
                    </h2>

                    <div class="space-y-4">
                        <label class="flex items-center p-4 border border-brand-gold/30 rounded-xl cursor-pointer hover:bg-brand-cultured/50 transition-colors">
                            <input type="radio" name="payment_method" value="razorpay" class="text-brand-gold focus:ring-brand-gold w-4 h-4" checked required>
                            <span class="ml-3 font-medium text-brand-wine">Credit/Debit Card / UPI / Netbanking</span>
                            <div class="ml-auto flex gap-2 text-brand-wine/50 text-xl">
                                <i class="fab fa-cc-visa"></i>
                                <i class="fab fa-cc-mastercard"></i>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-brand-gold/30 rounded-xl cursor-pointer hover:bg-brand-cultured/50 transition-colors">
                            <input type="radio" name="payment_method" value="cod" class="text-brand-gold focus:ring-brand-gold w-4 h-4">
                            <span class="ml-3 font-medium text-brand-wine">Cash on Delivery</span>
                            <i class="fas fa-money-bill-wave ml-auto text-brand-wine/50 text-xl"></i>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-brand-wine p-8 rounded-3xl shadow-lg border border-brand-gold/20 text-white sticky top-32">
                    <h3 class="text-xl font-bold mb-6 text-brand-gold">Order Summary</h3>

                    <div class="space-y-4 mb-6 text-sm font-light text-white/80 border-b border-brand-gold/20 pb-6">
                        <div class="flex justify-between items-center">
                            <span>Subtotal (<?php echo count($cartItems); ?> items)</span>
                            <span class="font-medium">₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>GST (3%)</span>
                            <span class="font-medium">₹<?php echo number_format($gst, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Shipping</span>
                            <span class="font-medium">
                                <?php echo $shipping == 0 ? '<span class="text-brand-gold font-bold">FREE</span>' : '₹' . number_format($shipping, 2); ?>
                            </span>
                        </div>
                        <?php if ($redemptionValue > 0): ?>
                        <div class="flex justify-between items-center text-brand-lightgold pt-2 border-t border-brand-gold/10 mt-2">
                            <span>Scheme Redemption (<?php echo number_format($redemptionGrams, 4); ?>g)</span>
                            <span class="font-medium">- ₹<?php echo number_format($redemptionValue, 2); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-lg font-bold">Total Payable</span>
                            <span class="text-2xl font-bold text-brand-gold">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <p class="text-[10px] text-right text-white/50">Inclusive of all taxes</p>
                    </div>

                    <button type="submit" id="place-order-btn" class="w-full bg-brand-gold text-brand-wine py-4 rounded-xl font-bold shadow-xl hover:bg-brand-lightgold transition-all duration-300 flex justify-center items-center">
                        <i class="fas fa-lock mr-2 text-sm"></i> Place Order Securely
                    </button>

                    <p class="text-xs text-center text-white/50 mt-4 font-light">
                        By placing your order, you agree to our <a href="#" class="underline hover:text-brand-gold">Terms of Service</a> and <a href="#" class="underline hover:text-brand-gold">Privacy Policy</a>.
                    </p>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    const btn = document.getElementById('place-order-btn');
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2 text-sm"></i> Processing...';
    btn.disabled = true;
    btn.classList.add('opacity-80', 'cursor-not-allowed');
    // Let the form submit normally to the API
});
</script>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>