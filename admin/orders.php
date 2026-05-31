<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

// Fetch Orders
$query = "
    SELECT o.id, o.final_amount, o.status, o.created_at, u.first_name, u.last_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";
$orders = $pdo->query($query)->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
        <p class="text-gray-500 mt-1 text-sm">Track, manage, and update customer orders.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 justify-between bg-gray-50">
        <div class="relative w-full sm:w-64">
            <input type="text" placeholder="Search orders..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-1 focus:ring-brand-gold">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
        </div>
        <div class="flex gap-2">
            <select class="border border-gray-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-brand-gold">
                <option>All Status</option>
                <option>Pending</option>
                <option>Processing</option>
                <option>Shipped</option>
                <option>Delivered</option>
                <option>Cancelled</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">Order ID</th>
                    <th class="px-6 py-4 font-medium">Customer</th>
                    <th class="px-6 py-4 font-medium">Date</th>
                    <th class="px-6 py-4 font-medium">Amount</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-50">
                <?php if (empty($orders)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No orders found.</td></tr>
                <?php else: foreach ($orders as $o): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-brand-wine">
                        #<?php echo str_pad($o['id'], 6, '0', STR_PAD_LEFT); ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($o['first_name'] . ' ' . $o['last_name']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($o['email']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        <?php echo date('M j, Y h:i A', strtotime($o['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900">
                        ₹<?php echo number_format($o['final_amount'], 2); ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                        $statusClass = '';
                        switch($o['status']) {
                            case 'delivered': $statusClass = 'bg-green-100 text-green-700'; break;
                            case 'pending': $statusClass = 'bg-orange-100 text-orange-700'; break;
                            case 'cancelled': $statusClass = 'bg-red-100 text-red-700'; break;
                            default: $statusClass = 'bg-blue-100 text-blue-700';
                        }
                        ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($o['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="/admin/order-details.php?id=<?php echo $o['id']; ?>" class="text-brand-gold hover:text-brand-wine transition-colors text-sm font-medium">View / Update</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>