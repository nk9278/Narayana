<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

// Fetch Redemptions
$query = "
    SELECT r.*, e.total_gold_reserved_grams, u.first_name, u.last_name, u.email, s.name as scheme_name
    FROM scheme_redemptions r
    JOIN scheme_enrollments e ON r.enrollment_id = e.id
    JOIN savings_schemes s ON e.scheme_id = s.id
    JOIN users u ON e.user_id = u.id
    ORDER BY r.redemption_date DESC
";
$redemptions = $pdo->query($query)->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Redemption History</h1>
        <p class="text-gray-500 mt-1 text-sm">Review user gold redemptions against jewelry purchases.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h2 class="font-bold text-gray-700">Completed Redemptions</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">Date</th>
                    <th class="px-6 py-4 font-medium">Customer</th>
                    <th class="px-6 py-4 font-medium">Scheme</th>
                    <th class="px-6 py-4 font-medium">Redeemed Gold</th>
                    <th class="px-6 py-4 font-medium">Redeemed Value</th>
                    <th class="px-6 py-4 font-medium">Linked Order</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-50">
                <?php if (empty($redemptions)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No redemptions have been processed yet.</td></tr>
                <?php else: foreach ($redemptions as $r): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-gray-500"><?php echo date('M j, Y', strtotime($r['redemption_date'])); ?></td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($r['email']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-brand-wine font-medium"><?php echo htmlspecialchars($r['scheme_name']); ?></td>
                    <td class="px-6 py-4 font-bold text-brand-gold"><?php echo number_format($r['redeemed_gold_grams'], 4); ?>g</td>
                    <td class="px-6 py-4 font-medium text-green-600">₹<?php echo number_format($r['value_in_currency'], 2); ?></td>
                    <td class="px-6 py-4">
                        <a href="/admin/order-details.php?id=<?php echo $r['order_id']; ?>" class="text-brand-gold hover:text-brand-wine hover:underline">
                            #<?php echo str_pad($r['order_id'], 6, '0', STR_PAD_LEFT); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>