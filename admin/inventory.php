<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $variant_id = filter_input(INPUT_POST, 'variant_id', FILTER_VALIDATE_INT);
    $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);

    if ($variant_id && $stock_quantity !== false) {
        try {
            $stmt = $pdo->prepare("UPDATE product_inventory SET stock_quantity = ? WHERE variant_id = ?");
            $stmt->execute([$stock_quantity, $variant_id]);
            $success = "Stock updated successfully.";
        } catch(PDOException $e) {
            $error = "Failed to update stock: " . $e->getMessage();
        }
    }
}

// Fetch Inventory Data
$query = "
    SELECT v.id as variant_id, v.sku, v.weight_grams, v.purity,
           p.name as product_name, c.name as category_name,
           i.stock_quantity
    FROM product_variants v
    JOIN products p ON v.product_id = p.id
    JOIN subcategories s ON p.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    LEFT JOIN product_inventory i ON v.id = i.variant_id
    ORDER BY i.stock_quantity ASC, p.name ASC
";
$inventory = $pdo->query($query)->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inventory Management</h1>
        <p class="text-gray-500 mt-1 text-sm">Monitor and update product variant stock levels.</p>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
        <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-6 border border-red-200">
        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 justify-between bg-gray-50">
        <div class="relative w-full sm:w-64">
            <input type="text" placeholder="Search SKU or Product..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-1 focus:ring-brand-gold">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">SKU</th>
                    <th class="px-6 py-4 font-medium">Product Name</th>
                    <th class="px-6 py-4 font-medium">Variant Details</th>
                    <th class="px-6 py-4 font-medium">Stock Status</th>
                    <th class="px-6 py-4 font-medium text-right">Update Stock</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-50">
                <?php if (empty($inventory)): ?>
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No inventory variants found.</td></tr>
                <?php else: foreach ($inventory as $item):
                    $stock = (int)$item['stock_quantity'];
                    $stockClass = $stock <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
                    $stockText = $stock <= 5 ? 'Low Stock' : 'In Stock';
                    if ($stock == 0) { $stockText = 'Out of Stock'; $stockClass = 'bg-gray-100 text-gray-700'; }
                ?>
                <tr class="hover:bg-gray-50 transition-colors <?php echo $stock <= 5 ? 'bg-red-50/30' : ''; ?>">
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">
                        <?php echo htmlspecialchars($item['sku']); ?>
                    </td>
                    <td class="px-6 py-4 font-medium text-brand-wine">
                        <?php echo htmlspecialchars($item['product_name']); ?>
                        <div class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($item['category_name']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        <?php echo htmlspecialchars($item['weight_grams']); ?>g / <?php echo htmlspecialchars($item['purity']); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $stockClass; ?>">
                            <?php echo $stockText; ?> (<?php echo $stock; ?>)
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form method="POST" class="inline-flex items-center justify-end space-x-2">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="variant_id" value="<?php echo $item['variant_id']; ?>">
                            <input type="number" name="stock_quantity" value="<?php echo $stock; ?>" min="0" class="w-20 border border-gray-300 rounded text-sm px-2 py-1 focus:ring-brand-gold focus:border-brand-gold text-center">
                            <button type="submit" class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded hover:bg-black transition-colors">
                                Save
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>