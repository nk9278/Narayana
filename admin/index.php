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

        <!-- Recent Orders Table -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Recent Orders</h3>
                <a href="#" class="text-sm text-brand-gold hover:text-brand-wine">View All</a>
            </div>
            <div class="p-6 text-center text-gray-500 py-12">
                No orders have been placed yet.
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