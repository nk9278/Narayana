<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

// Fetch Schemes with Analytics
$query = "
    SELECT s.*,
           (SELECT COUNT(*) FROM scheme_enrollments e WHERE e.scheme_id = s.id AND e.status = 'active') as active_enrollments,
           (SELECT SUM(total_paid) FROM scheme_enrollments e WHERE e.scheme_id = s.id) as total_collected
    FROM savings_schemes s
    ORDER BY s.created_at DESC
";
$schemes = $pdo->query($query)->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Savings Schemes Management</h1>
        <p class="text-gray-500 mt-1 text-sm">Create and manage gold savings programs for your customers.</p>
    </div>
    <a href="/admin/add-scheme.php" class="bg-brand-gold text-brand-wine px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-brand-lightgold transition-colors">
        <i class="fas fa-plus mr-2"></i>Create Scheme
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">Scheme Name</th>
                    <th class="px-6 py-4 font-medium">Monthly Amount</th>
                    <th class="px-6 py-4 font-medium">Duration</th>
                    <th class="px-6 py-4 font-medium">Enrollments</th>
                    <th class="px-6 py-4 font-medium">Total Collected</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-50">
                <?php if (empty($schemes)): ?>
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No savings schemes defined. Create one to get started.</td></tr>
                <?php else: foreach ($schemes as $s): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-brand-wine"><?php echo htmlspecialchars($s['name']); ?></div>
                    </td>
                    <td class="px-6 py-4 font-medium">₹<?php echo number_format($s['monthly_amount'], 2); ?></td>
                    <td class="px-6 py-4 text-gray-500"><?php echo $s['duration_months']; ?> Months</td>
                    <td class="px-6 py-4 font-medium text-blue-600"><?php echo number_format($s['active_enrollments']); ?> Active</td>
                    <td class="px-6 py-4 font-medium text-green-600">₹<?php echo number_format($s['total_collected'] ?? 0, 2); ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $s['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="/admin/edit-scheme.php?id=<?php echo $s['id']; ?>" class="text-brand-gold hover:text-brand-wine transition-colors"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>