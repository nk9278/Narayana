<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/includes/header.php';

// Fetch user data here in production
$first_name = "Guest";
$totalOrders = 0;
$activeSchemes = 0;
$wishlistItems = 0; // Using a placeholder for wishlist table, adjust as per schema

if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT first_name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $first_name = $stmt->fetchColumn() ?: "Guest";

    $stmtOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmtOrders->execute([$_SESSION['user_id']]);
    $totalOrders = $stmtOrders->fetchColumn();

    $stmtSchemes = $pdo->prepare("SELECT COUNT(*) FROM scheme_enrollments WHERE user_id = ? AND status = 'active'");
    $stmtSchemes->execute([$_SESSION['user_id']]);
    $activeSchemes = $stmtSchemes->fetchColumn();

    // In actual implementation this would join a wishlist table, for now we will stub it to 0 as it was omitted from the original schema
    $wishlistItems = 0;
}

?>

<?php include_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto bg-brand-cultured p-6 md:p-10">

    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-bold text-brand-wine">Welcome back, <?php echo htmlspecialchars($first_name); ?>!</h1>
            <p class="text-brand-wine/70 mt-1 font-light">Here is a summary of your account activity.</p>
        </div>
        <a href="/" class="hidden md:inline-flex px-6 py-2.5 bg-white border border-brand-gold/30 text-brand-wine rounded-full hover:bg-brand-gold hover:text-white transition-colors shadow-sm font-medium text-sm">
            Continue Shopping
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Dashboard Cards -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-brand-gold/20 flex items-center justify-between">
            <div>
                <p class="text-brand-wine/60 text-sm font-medium mb-1">Total Orders</p>
                <h3 class="text-3xl font-bold text-brand-wine"><?php echo number_format($totalOrders); ?></h3>
            </div>
            <div class="w-14 h-14 bg-brand-gold/10 rounded-full flex items-center justify-center text-brand-gold">
                <i class="fas fa-box-open text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-brand-gold/20 flex items-center justify-between">
            <div>
                <p class="text-brand-wine/60 text-sm font-medium mb-1">Active Schemes</p>
                <h3 class="text-3xl font-bold text-brand-wine"><?php echo number_format($activeSchemes); ?></h3>
            </div>
            <div class="w-14 h-14 bg-brand-gold/10 rounded-full flex items-center justify-center text-brand-gold">
                <i class="fas fa-piggy-bank text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-brand-gold/20 flex items-center justify-between">
            <div>
                <p class="text-brand-wine/60 text-sm font-medium mb-1">Wishlist Items</p>
                <h3 class="text-3xl font-bold text-brand-wine"><?php echo number_format($wishlistItems); ?></h3>
            </div>
            <div class="w-14 h-14 bg-brand-gold/10 rounded-full flex items-center justify-center text-brand-gold">
                <i class="far fa-heart text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-brand-gold/20 overflow-hidden mb-10">
        <div class="px-6 py-5 border-b border-brand-gold/10 flex justify-between items-center bg-brand-wine/5">
            <h3 class="font-bold text-brand-wine text-lg">Recent Orders</h3>
            <a href="/user-dashboard/orders.php" class="text-brand-gold text-sm font-medium hover:text-brand-wine transition-colors">View All</a>
        </div>
        <?php
        $recentOrders = $pdo->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
        $recentOrders->execute([$_SESSION['user_id']]);
        $orders = $recentOrders->fetchAll();

        if (empty($orders)):
        ?>
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-brand-cultured rounded-full flex items-center justify-center mx-auto mb-4 text-brand-gold/50">
                <i class="fas fa-shopping-bag text-3xl"></i>
            </div>
            <p class="text-brand-wine/70 mb-4">You haven't placed any orders yet.</p>
            <a href="/" class="inline-block px-8 py-3 bg-brand-wine text-white rounded-full font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                Start Shopping
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-brand-wine/50 text-xs uppercase tracking-wider border-b border-brand-gold/10">
                        <th class="px-6 py-4 font-medium">Order ID</th>
                        <th class="px-6 py-4 font-medium">Date</th>
                        <th class="px-6 py-4 font-medium">Total</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-brand-wine">
                    <?php foreach ($orders as $order): ?>
                    <tr class="border-b border-brand-gold/5 hover:bg-brand-cultured/30 transition-colors">
                        <td class="px-6 py-4 font-medium">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td class="px-6 py-4 text-brand-wine/70"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        <td class="px-6 py-4 font-bold">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $order['status'] === 'delivered' ? 'bg-green-100 text-green-700' : ($order['status'] === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-brand-gold/20 text-brand-wine'); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="/user-dashboard/order-details.php?id=<?php echo $order['id']; ?>" class="text-brand-gold font-medium hover:text-brand-wine transition-colors text-sm">View Details</a>
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