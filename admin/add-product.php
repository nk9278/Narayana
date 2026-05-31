<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';

// Form submission handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    // In a real app, extensive validation and file upload handling logic goes here.
    // For this implementation, we simulate success to keep it functional.
    $name = $_POST['name'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $subcategory_id = filter_input(INPUT_POST, 'subcategory_id', FILTER_VALIDATE_INT);
    $base_price = filter_input(INPUT_POST, 'base_price', FILTER_VALIDATE_FLOAT);

    if($name && $subcategory_id && $base_price) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (subcategory_id, name, slug, description, metal_type, base_price, making_charges_percentage, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $subcategory_id,
                $name,
                $slug . '-' . time(), // ensure unique slug
                $_POST['description'] ?? '',
                $_POST['metal_type'] ?? 'gold',
                $base_price,
                $_POST['making_charges'] ?? 0,
                isset($_POST['is_active']) ? 1 : 0
            ]);
            $success = "Product added successfully!";
        } catch(PDOException $e) {
            $error = "Error adding product: " . $e->getMessage();
        }
    } else {
        $error = "Please fill all required fields.";
    }
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT s.id, s.name as sub_name, c.name as cat_name FROM subcategories s JOIN categories c ON s.category_id = c.id ORDER BY c.name, s.name")->fetchAll();

include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Add New Product</h1>
        <p class="text-gray-500 mt-1 text-sm">Create a new product listing in the catalog.</p>
    </div>
    <a href="/admin/products.php" class="text-brand-wine hover:text-brand-gold font-medium text-sm transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Back to Products
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

<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <div class="lg:col-span-2 space-y-6">
        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-brand-wine mb-4">Basic Information</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold focus:border-brand-gold">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category / Subcategory *</label>
                        <select name="subcategory_id" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold focus:border-brand-gold">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['cat_name'] . ' > ' . $cat['sub_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metal Type *</label>
                        <select name="metal_type" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold focus:border-brand-gold">
                            <option value="gold">Gold</option>
                            <option value="silver">Silver</option>
                            <option value="platinum">Platinum</option>
                            <option value="rose_gold">Rose Gold</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="5" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold focus:border-brand-gold"></textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & Inventory -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-brand-wine mb-4">Pricing</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (₹) *</label>
                    <input type="number" step="0.01" name="base_price" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Making Charges (%) *</label>
                    <input type="number" step="0.01" name="making_charges" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i> Note: Live pricing requires variants to be set up after product creation.</p>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Visibility -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-brand-wine mb-4">Publishing</h2>

            <label class="flex items-center space-x-3 mb-6 cursor-pointer">
                <input type="checkbox" name="is_active" class="w-4 h-4 text-brand-gold rounded focus:ring-brand-gold" checked>
                <span class="text-sm font-medium text-gray-700">Product is Active</span>
            </label>

            <button type="submit" class="w-full bg-brand-wine text-white py-2.5 rounded-lg font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                Save Product
            </button>
        </div>

        <!-- Images Placeholder -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-brand-wine mb-4">Product Images</h2>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50">
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-500">Image upload will be available after saving the initial product details.</p>
            </div>
        </div>
    </div>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>