<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    echo "<div class='p-8'>Invalid order ID.</div>";
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    if (in_array($new_status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $success = "Order status updated to " . ucfirst($new_status) . ".";
    }
}

// Fetch Order Details
$stmt = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone,
           a.address_line1, a.address_line2, a.city, a.state, a.pincode, a.country,
           t.transaction_id, t.payment_method, t.status as payment_status
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN user_addresses a ON o.address_id = a.id
    LEFT JOIN transactions t ON o.id = t.order_id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='p-8'>Order not found.</div>";
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch Order Items
$stmtItems = $pdo->prepare("
    SELECT oi.*, p.name, p.slug, v.sku, v.weight_grams, v.purity,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as image_url
    FROM order_items oi
    JOIN product_variants v ON oi.variant_id = v.id
    JOIN products p ON v.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$orderItems = $stmtItems->fetchAll();

?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
        <p class="text-gray-500 mt-1 text-sm">Placed on <?php echo date('F j, Y h:i A', strtotime($order['created_at'])); ?></p>
    </div>
    <a href="/admin/orders.php" class="text-brand-wine hover:text-brand-gold font-medium text-sm transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Back to Orders
    </a>
</div>

<?php if (isset($success)): ?>
    <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
        <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">

        <!-- Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 font-bold text-gray-800">
                Ordered Items
            </div>
            <div class="p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-gray-500 border-b border-gray-100">
                            <th class="pb-3">Product</th>
                            <th class="pb-3 text-center">Qty</th>
                            <th class="pb-3 text-right">Price</th>
                            <th class="pb-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/150'); ?>" class="w-12 h-12 object-cover rounded-lg mr-4 border border-gray-200">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5">SKU: <?php echo htmlspecialchars($item['sku']); ?> | <?php echo htmlspecialchars($item['weight_grams']); ?>g <?php echo htmlspecialchars($item['purity']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-center text-gray-600"><?php echo $item['quantity']; ?></td>
                            <td class="py-4 text-right text-gray-600">₹<?php echo number_format($item['price_per_unit'], 2); ?></td>
                            <td class="py-4 text-right font-medium text-gray-900">₹<?php echo number_format($item['total_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mt-6 border-t border-gray-100 pt-6 space-y-3 text-sm flex justify-end">
                    <div class="w-64">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600 mt-2">
                            <span>Tax (GST)</span>
                            <span>₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600 mt-2">
                            <span>Shipping</span>
                            <span>₹<?php echo number_format($order['final_amount'] - $order['total_amount'] - $order['tax_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between font-bold text-gray-900 text-lg mt-4 border-t border-gray-200 pt-4">
                            <span>Total</span>
                            <span>₹<?php echo number_format($order['final_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <!-- Status Update -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4">Order Status</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="update_status">

                <select name="status" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>

                <button type="submit" class="w-full bg-brand-wine text-white py-2 rounded-lg font-medium hover:bg-brand-burgundy transition-colors">
                    Update Status
                </button>
            </form>
        </div>

        <!-- Customer & Shipping -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4">Customer Details</h2>
            <div class="text-sm text-gray-600 space-y-2">
                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                <p><i class="fas fa-envelope w-5 text-gray-400"></i> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><i class="fas fa-phone w-5 text-gray-400"></i> <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>

            <h2 class="font-bold text-gray-800 mt-6 mb-4">Shipping Address</h2>
            <div class="text-sm text-gray-600">
                <p><?php echo htmlspecialchars($order['address_line1']); ?></p>
                <?php if($order['address_line2']) echo "<p>" . htmlspecialchars($order['address_line2']) . "</p>"; ?>
                <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['pincode']); ?></p>
                <p><?php echo htmlspecialchars($order['country']); ?></p>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4">Payment Information</h2>
            <div class="text-sm text-gray-600 space-y-3">
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-500">Method</span>
                    <span class="font-medium uppercase"><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-500">Transaction ID</span>
                    <span class="font-mono text-xs"><?php echo htmlspecialchars($order['transaction_id'] ?? 'Pending'); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="font-bold <?php echo ($order['payment_status'] ?? '') === 'success' ? 'text-green-600' : 'text-orange-500'; ?>">
                        <?php echo ucfirst(htmlspecialchars($order['payment_status'] ?? 'Pending')); ?>
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>