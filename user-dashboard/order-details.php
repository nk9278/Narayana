<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    echo "<div class='p-8'>Invalid order ID.</div>";
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch Order Details ensuring it belongs to the logged-in user
$stmt = $pdo->prepare("
    SELECT o.*,
           a.address_line1, a.address_line2, a.city, a.state, a.pincode, a.country,
           t.transaction_id, t.payment_method
    FROM orders o
    JOIN user_addresses a ON o.address_id = a.id
    LEFT JOIN transactions t ON o.id = t.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='p-8 text-center text-red-500'>Order not found or access denied.</div>";
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

<main class="flex-1 overflow-y-auto bg-brand-cultured p-6 md:p-10">
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-brand-wine">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
            <p class="text-brand-wine/70 mt-1 font-light">Placed on <?php echo date('F j, Y h:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $order['status'] === 'delivered' ? 'bg-green-100 text-green-700' : ($order['status'] === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-brand-gold/20 text-brand-wine'); ?>">
                Status: <?php echo htmlspecialchars($order['status']); ?>
            </span>
            <a href="/user-dashboard/orders.php" class="text-brand-wine hover:text-brand-gold font-medium text-sm transition-colors bg-white px-4 py-2 rounded-lg border border-brand-gold/30">
                Back to Orders
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <!-- Items -->
            <div class="bg-white rounded-2xl shadow-sm border border-brand-gold/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-brand-gold/10 bg-brand-wine/5 font-bold text-brand-wine">
                    Items in your order
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="flex flex-col sm:flex-row gap-4 pb-6 border-b border-gray-100 last:border-0 last:pb-0">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/150'); ?>" class="w-24 h-24 object-cover rounded-xl border border-gray-200">
                            <div class="flex-grow flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-brand-wine hover:text-brand-burgundy transition-colors text-lg">
                                        <a href="/product.php?slug=<?php echo urlencode($item['slug']); ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                    </h3>
                                    <p class="text-xs text-brand-wine/60 mt-1 font-light">SKU: <?php echo htmlspecialchars($item['sku']); ?> | <?php echo htmlspecialchars($item['weight_grams']); ?>g <?php echo htmlspecialchars($item['purity']); ?></p>
                                </div>
                                <div class="mt-2 flex justify-between items-end">
                                    <p class="text-sm text-gray-500">Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price_per_unit'], 2); ?></p>
                                    <p class="font-bold text-brand-wine">₹<?php echo number_format($item['total_price'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tracking (Mockup) -->
            <div class="bg-white rounded-2xl shadow-sm border border-brand-gold/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-brand-gold/10 bg-brand-wine/5 font-bold text-brand-wine">
                    Order Tracking
                </div>
                <div class="p-6">
                    <div class="relative pl-6 space-y-8 before:content-[''] before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-0.5 before:bg-brand-gold/30">
                        <div class="relative">
                            <div class="absolute -left-8 w-4 h-4 rounded-full bg-brand-gold ring-4 ring-white"></div>
                            <h4 class="font-bold text-brand-wine text-sm">Order Placed</h4>
                            <p class="text-xs text-gray-500 mt-1"><?php echo date('M j, Y h:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="relative opacity-<?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? '100' : '40'; ?>">
                            <div class="absolute -left-8 w-4 h-4 rounded-full <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'bg-brand-gold' : 'bg-gray-300'; ?> ring-4 ring-white"></div>
                            <h4 class="font-bold text-brand-wine text-sm">Processing</h4>
                        </div>
                        <div class="relative opacity-<?php echo in_array($order['status'], ['shipped', 'delivered']) ? '100' : '40'; ?>">
                            <div class="absolute -left-8 w-4 h-4 rounded-full <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'bg-brand-gold' : 'bg-gray-300'; ?> ring-4 ring-white"></div>
                            <h4 class="font-bold text-brand-wine text-sm">Shipped</h4>
                        </div>
                        <div class="relative opacity-<?php echo $order['status'] === 'delivered' ? '100' : '40'; ?>">
                            <div class="absolute -left-8 w-4 h-4 rounded-full <?php echo $order['status'] === 'delivered' ? 'bg-brand-gold' : 'bg-gray-300'; ?> ring-4 ring-white"></div>
                            <h4 class="font-bold text-brand-wine text-sm">Delivered</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <!-- Order Summary -->
            <div class="bg-brand-wine rounded-2xl shadow-lg border border-brand-gold/20 p-6 text-white">
                <h3 class="font-bold text-brand-gold mb-4 border-b border-brand-gold/20 pb-2">Order Summary</h3>
                <div class="space-y-3 text-sm font-light text-white/80">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax (GST)</span>
                        <span>₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping</span>
                        <span>₹<?php echo number_format($order['final_amount'] - $order['total_amount'] - $order['tax_amount'], 2); ?></span>
                    </div>
                    <div class="flex justify-between font-bold text-brand-gold text-lg mt-4 border-t border-brand-gold/20 pt-4">
                        <span>Total Paid</span>
                        <span>₹<?php echo number_format($order['final_amount'], 2); ?></span>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-brand-gold/20 flex items-center justify-between text-xs text-white/60">
                    <span>Payment Method: <strong class="uppercase text-white"><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></strong></span>
                </div>
            </div>

            <!-- Shipping Details -->
            <div class="bg-white rounded-2xl shadow-sm border border-brand-gold/20 p-6">
                <h3 class="font-bold text-brand-wine mb-4 border-b border-gray-100 pb-2">Shipping Address</h3>
                <div class="text-sm text-gray-600 space-y-1 font-light">
                    <p><?php echo htmlspecialchars($order['address_line1']); ?></p>
                    <?php if($order['address_line2']) echo "<p>" . htmlspecialchars($order['address_line2']) . "</p>"; ?>
                    <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['pincode']); ?></p>
                    <p><?php echo htmlspecialchars($order['country']); ?></p>
                </div>
            </div>

            <button class="w-full border-2 border-brand-gold text-brand-wine py-3 rounded-xl font-bold hover:bg-brand-gold hover:text-white transition-colors">
                <i class="fas fa-file-invoice mr-2"></i> Download Invoice
            </button>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>