<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$stmt = $pdo->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<main class="flex-1 overflow-y-auto bg-brand-cultured p-6 md:p-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-brand-wine">My Orders</h1>
        <p class="text-brand-wine/70 mt-1 font-light">View and track your previous purchases.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-brand-gold/20 overflow-hidden">
        <?php if (empty($orders)): ?>
        <div class="p-12 text-center">
            <div class="w-24 h-24 bg-brand-cultured rounded-full flex items-center justify-center mx-auto mb-6 text-brand-gold/50">
                <i class="fas fa-shopping-bag text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-brand-wine mb-2">No orders found</h3>
            <p class="text-brand-wine/70 mb-8 font-light">You haven't placed any orders yet. Discover our premium collection today.</p>
            <a href="/" class="inline-flex bg-brand-wine text-white py-3 px-8 rounded-full font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                Start Shopping
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-brand-wine/50 text-xs uppercase tracking-wider border-b border-brand-gold/10">
                        <th class="px-6 py-4 font-medium">Order ID</th>
                        <th class="px-6 py-4 font-medium">Date</th>
                        <th class="px-6 py-4 font-medium">Total Amount</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-brand-wine divide-y divide-brand-gold/5">
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-brand-cultured/30 transition-colors">
                        <td class="px-6 py-5 font-medium">
                            #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="px-6 py-5 text-brand-wine/70">
                            <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                        </td>
                        <td class="px-6 py-5 font-bold">
                            ₹<?php echo number_format($order['total_amount'], 2); ?>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $order['status'] === 'delivered' ? 'bg-green-100 text-green-700' : ($order['status'] === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-brand-gold/20 text-brand-wine'); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="/user-dashboard/order-details.php?id=<?php echo $order['id']; ?>" class="inline-flex items-center text-brand-gold font-medium hover:text-brand-wine transition-colors text-sm border border-brand-gold/30 px-3 py-1.5 rounded-lg hover:bg-brand-gold/5">
                                View Details <i class="fas fa-chevron-right ml-2 text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>