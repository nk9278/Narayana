<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $delete_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$delete_id]);
            $success = "Product deleted successfully.";
        } catch(PDOException $e) {
            $error = "Cannot delete product. It may be linked to existing orders.";
        }
    }
}

// Fetch Products
$query = "
    SELECT p.id, p.name, p.base_price, p.is_active, p.created_at,
           c.name as category_name, s.name as subcategory_name,
           (SELECT SUM(stock_quantity) FROM product_inventory pi JOIN product_variants pv ON pi.variant_id = pv.id WHERE pv.product_id = p.id) as total_stock
    FROM products p
    JOIN subcategories s ON p.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    ORDER BY p.created_at DESC
";
$products = $pdo->query($query)->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Products Catalog</h1>
        <p class="text-gray-500 mt-1 text-sm">Manage your entire inventory and product details.</p>
    </div>
    <a href="/admin/add-product.php" class="bg-brand-wine text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-burgundy transition-colors shadow-md">
        <i class="fas fa-plus mr-2"></i>Add Product
    </a>
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
            <input type="text" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-1 focus:ring-brand-gold">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
        </div>
        <div class="flex gap-2">
            <select class="border border-gray-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-brand-gold">
                <option>All Categories</option>
                <option>Rings</option>
                <option>Necklaces</option>
            </select>
            <select class="border border-gray-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-brand-gold">
                <option>All Status</option>
                <option>Active</option>
                <option>Inactive</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">Product details</th>
                    <th class="px-6 py-4 font-medium">Category</th>
                    <th class="px-6 py-4 font-medium">Price</th>
                    <th class="px-6 py-4 font-medium">Stock</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-50">
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No products found.</td></tr>
                <?php else: foreach ($products as $p): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-brand-wine"><?php echo htmlspecialchars($p['name']); ?></div>
                        <div class="text-xs text-gray-400 mt-1">ID: #<?php echo $p['id']; ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div><?php echo htmlspecialchars($p['category_name']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($p['subcategory_name']); ?></div>
                    </td>
                    <td class="px-6 py-4 font-medium">₹<?php echo number_format($p['base_price'], 2); ?></td>
                    <td class="px-6 py-4">
                        <?php $stock = (int)$p['total_stock']; ?>
                        <span class="<?php echo $stock > 0 ? 'text-green-600' : 'text-red-600'; ?> font-medium">
                            <?php echo $stock; ?> units
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" <?php echo $p['is_active'] ? 'checked' : ''; ?> disabled>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                        </label>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="/admin/edit-product.php?id=<?php echo $p['id']; ?>" class="text-brand-gold hover:text-brand-wine transition-colors"><i class="fas fa-edit"></i></a>
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Placeholder -->
    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>Showing 1 to <?php echo count($products); ?> of <?php echo count($products); ?> entries</span>
        <div class="flex gap-1">
            <button class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-200 rounded bg-brand-wine text-white">1</button>
            <button class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>