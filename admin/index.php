<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

// Fetch Dynamic Admin Metrics
$totalRevenue = $pdo->query("SELECT SUM(final_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$activeSchemes = $pdo->query("SELECT COUNT(*) FROM scheme_enrollments WHERE status = 'active'")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<!-- Admin Dashboard Content -->

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
        <p class="text-gray-500 mt-1 text-sm">Key metrics and recent activity for your platform.</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-xl mr-4">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                <h3 class="text-2xl font-bold text-gray-800">₹<?php echo number_format($totalRevenue, 2); ?></h3>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl mr-4">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Orders</p>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalOrders); ?></h3>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-brand-gold/10 text-brand-gold flex items-center justify-center text-xl mr-4">
                <i class="fas fa-piggy-bank"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Active Schemes</p>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($activeSchemes); ?></h3>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xl mr-4">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Customers</p>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalCustomers); ?></h3>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Products Management Quick View -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800">Recent Products</h3>
                <div class="space-x-3">
                    <a href="/admin/products.php" class="text-sm text-brand-gold hover:text-brand-wine">View All</a>
                    <a href="/admin/add-product.php" class="text-sm bg-brand-wine text-white px-3 py-1.5 rounded hover:bg-brand-burgundy transition-colors"><i class="fas fa-plus mr-1"></i> Add Product</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-medium">Product</th>
                            <th class="px-6 py-4 font-medium">Category</th>
                            <th class="px-6 py-4 font-medium">Price</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php
                        $products = $pdo->query("SELECT p.id, p.name, p.base_price, p.is_active, c.name as category_name FROM products p JOIN subcategories s ON p.subcategory_id = s.id JOIN categories c ON s.category_id = c.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
                        if (empty($products)):
                        ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No products found. Add your first product to get started.</td>
                        </tr>
                        <?php else: foreach ($products as $p): ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-brand-wine"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($p['category_name']); ?></td>
                            <td class="px-6 py-4">₹<?php echo number_format($p['base_price'], 2); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs <?php echo $p['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                    <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="/admin/edit-product.php?id=<?php echo $p['id']; ?>" class="text-brand-gold hover:text-brand-wine"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Orders (Admin) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800">Recent Orders</h3>
                <a href="/admin/orders.php" class="text-sm text-brand-gold hover:text-brand-wine">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-medium">Order ID</th>
                            <th class="px-6 py-4 font-medium">Customer</th>
                            <th class="px-6 py-4 font-medium">Amount</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php
                        $ordersList = $pdo->query("
                            SELECT o.id, o.final_amount, o.status, u.first_name, u.last_name
                            FROM orders o
                            JOIN users u ON o.user_id = u.id
                            ORDER BY o.created_at DESC LIMIT 5
                        ")->fetchAll();
                        if (empty($ordersList)):
                        ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No orders received yet.</td>
                        </tr>
                        <?php else: foreach ($ordersList as $ord): ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-brand-wine">#<?php echo str_pad($ord['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($ord['first_name'] . ' ' . $ord['last_name']); ?></td>
                            <td class="px-6 py-4 font-medium">₹<?php echo number_format($ord['final_amount'], 2); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs uppercase font-semibold <?php echo $ord['status'] === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700'; ?>">
                                    <?php echo htmlspecialchars($ord['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/admin/order-details.php?id=<?php echo $ord['id']; ?>" class="text-brand-gold hover:text-brand-wine">Process</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Live Gold Rates Widget -->
        <div class="bg-white rounded-xl shadow-sm border border-brand-gold/30 overflow-hidden relative">
            <div class="absolute top-0 w-full h-1 bg-brand-gold"></div>
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-brand-wine">Live Metal Rates</h3>
                <p class="text-xs text-gray-500 mt-1">Today's base rates</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="font-medium text-gray-700">24K Gold</span>
                    <span class="font-bold text-brand-wine">₹7,200 /g</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="font-medium text-gray-700">22K Gold</span>
                    <span class="font-bold text-brand-wine">₹6,600 /g</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="font-medium text-gray-700">Silver</span>
                    <span class="font-bold text-gray-600">₹85 /g</span>
                </div>
                <button class="w-full mt-2 py-2 border border-brand-gold text-brand-gold rounded-lg text-sm font-medium hover:bg-brand-gold hover:text-white transition-colors">
                    Update Rates
                </button>
            </div>
        </div>

    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>