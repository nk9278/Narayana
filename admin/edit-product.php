<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    echo "<div class='p-8'>Invalid product ID.</div>";
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_basic') {
            $stmt = $pdo->prepare("UPDATE products SET subcategory_id=?, name=?, description=?, metal_type=?, base_price=?, making_charges_percentage=?, is_active=? WHERE id=?");
            $stmt->execute([
                $_POST['subcategory_id'], $_POST['name'], $_POST['description'], $_POST['metal_type'],
                $_POST['base_price'], $_POST['making_charges_percentage'], isset($_POST['is_active']) ? 1 : 0, $product_id
            ]);
            $success = "Product updated successfully.";

        } elseif ($action === 'add_variant') {
            $sku = strtoupper(uniqid('SKU_'));
            $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, sku, size, weight_grams, purity, additional_price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $product_id, $sku, $_POST['size'] ?? '', $_POST['weight_grams'], $_POST['purity'], $_POST['additional_price']
            ]);

            // Add initial inventory for the new variant
            $variant_id = $pdo->lastInsertId();
            $stmtInv = $pdo->prepare("INSERT INTO product_inventory (variant_id, stock_quantity) VALUES (?, ?)");
            $stmtInv->execute([$variant_id, $_POST['stock_quantity'] ?? 0]);

            $success = "Variant added successfully.";

        } elseif ($action === 'delete_variant') {
            $variant_id = filter_input(INPUT_POST, 'variant_id', FILTER_VALIDATE_INT);
            if ($variant_id) {
                $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ? AND product_id = ?");
                $stmt->execute([$variant_id, $product_id]);
                $success = "Variant deleted.";
            }

        } elseif ($action === 'upload_image') {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // Security: Validate file extension and MIME type
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);

                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (in_array($fileExtension, $allowedExtensions) && in_array($mimeType, $allowedMimeTypes)) {
                    $uploadDir = __DIR__ . '/../uploads/products/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                    // Generate safe, unique file name
                    $safeFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
                    $targetFile = $uploadDir . $safeFileName;

                    if (move_uploaded_file($fileTmpPath, $targetFile)) {
                        $imageUrl = '/uploads/products/' . $safeFileName;
                        $is_primary = isset($_POST['is_primary']) ? 1 : 0;

                        if ($is_primary) {
                            $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?")->execute([$product_id]);
                        }

                        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$product_id, $imageUrl, $is_primary]);
                        $success = "Image uploaded successfully.";
                    } else {
                        $error = "Failed to move uploaded file.";
                    }
                } else {
                    $error = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
                }
            } else {
                $error = "Image upload failed.";
            }

        } elseif ($action === 'delete_image') {
            $image_id = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
            if ($image_id) {
                $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
                $stmt->execute([$image_id, $product_id]);
                // Note: file unlink should also happen here in a real scenario
                $success = "Image deleted.";
            }
        }
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Fetch Product Data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='p-8'>Product not found.</div>";
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

$variants = $pdo->prepare("SELECT v.*, i.stock_quantity FROM product_variants v LEFT JOIN product_inventory i ON v.id = i.variant_id WHERE v.product_id = ?");
$variants->execute([$product_id]);
$variants = $variants->fetchAll();

$images = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$images->execute([$product_id]);
$images = $images->fetchAll();

$categories = $pdo->query("SELECT s.id, s.name as sub_name, c.name as cat_name FROM subcategories s JOIN categories c ON s.category_id = c.id ORDER BY c.name, s.name")->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Product: <?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="text-gray-500 mt-1 text-sm">Manage product details, variants, and gallery.</p>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Basic Information Form -->
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-brand-wine mb-4">Basic Information</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="update_basic">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-brand-gold focus:border-brand-gold">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="subcategory_id" required class="w-full border border-gray-300 rounded-lg py-2 px-3">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $product['subcategory_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['cat_name'] . ' > ' . $cat['sub_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metal Type</label>
                            <select name="metal_type" required class="w-full border border-gray-300 rounded-lg py-2 px-3">
                                <option value="gold" <?php echo $product['metal_type'] === 'gold' ? 'selected' : ''; ?>>Gold</option>
                                <option value="silver" <?php echo $product['metal_type'] === 'silver' ? 'selected' : ''; ?>>Silver</option>
                                <option value="platinum" <?php echo $product['metal_type'] === 'platinum' ? 'selected' : ''; ?>>Platinum</option>
                                <option value="rose_gold" <?php echo $product['metal_type'] === 'rose_gold' ? 'selected' : ''; ?>>Rose Gold</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (₹)</label>
                            <input type="number" step="0.01" name="base_price" value="<?php echo $product['base_price']; ?>" required class="w-full border border-gray-300 rounded-lg py-2 px-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Making Charges (%)</label>
                            <input type="number" step="0.01" name="making_charges_percentage" value="<?php echo $product['making_charges_percentage']; ?>" required class="w-full border border-gray-300 rounded-lg py-2 px-3">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="5" class="w-full border border-gray-300 rounded-lg py-2 px-3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <label class="flex items-center space-x-3 cursor-pointer pt-2">
                        <input type="checkbox" name="is_active" class="w-4 h-4 text-brand-gold rounded focus:ring-brand-gold" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700">Product is Active</span>
                    </label>

                    <button type="submit" class="bg-brand-wine text-white px-6 py-2 rounded-lg font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                        Update Details
                    </button>
                </div>
            </form>
        </div>

        <!-- Variants Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-brand-wine mb-4">Product Variants & Inventory</h2>

            <div class="overflow-x-auto mb-6">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-2 font-medium">SKU</th>
                            <th class="px-4 py-2 font-medium">Weight/Purity</th>
                            <th class="px-4 py-2 font-medium">Add. Price</th>
                            <th class="px-4 py-2 font-medium">Stock</th>
                            <th class="px-4 py-2 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($variants)): ?>
                            <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No variants added yet.</td></tr>
                        <?php else: foreach($variants as $var): ?>
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-2 text-gray-600 font-mono text-xs"><?php echo $var['sku']; ?></td>
                                <td class="px-4 py-2"><?php echo $var['weight_grams']; ?>g / <?php echo $var['purity']; ?></td>
                                <td class="px-4 py-2">₹<?php echo $var['additional_price']; ?></td>
                                <td class="px-4 py-2"><?php echo $var['stock_quantity']; ?></td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this variant?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="delete_variant">
                                        <input type="hidden" name="variant_id" value="<?php echo $var['id']; ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Variant Form -->
            <form method="POST" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="text-sm font-bold text-gray-800 mb-3">Add New Variant</h3>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="add_variant">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <input type="text" name="size" placeholder="Size (Optional)" class="w-full text-sm border border-gray-300 rounded px-3 py-1.5">
                    </div>
                    <div>
                        <input type="number" step="0.01" name="weight_grams" placeholder="Weight (g) *" required class="w-full text-sm border border-gray-300 rounded px-3 py-1.5">
                    </div>
                    <div>
                        <input type="text" name="purity" placeholder="Purity (e.g. 22K) *" required class="w-full text-sm border border-gray-300 rounded px-3 py-1.5">
                    </div>
                    <div>
                        <input type="number" step="0.01" name="additional_price" placeholder="Add. Price (₹) *" value="0" required class="w-full text-sm border border-gray-300 rounded px-3 py-1.5">
                    </div>
                    <div>
                        <input type="number" name="stock_quantity" placeholder="Initial Stock *" value="1" required class="w-full text-sm border border-gray-300 rounded px-3 py-1.5">
                    </div>
                </div>
                <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-1.5 rounded hover:bg-black transition-colors">
                    Add Variant
                </button>
            </form>
        </div>
    </div>

    <!-- Images Section -->
    <div class="space-y-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-brand-wine mb-4">Gallery Images</h2>

            <form method="POST" enctype="multipart/form-data" class="mb-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="upload_image">

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 mb-3 relative">
                    <input type="file" name="image" required accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <i class="fas fa-cloud-upload-alt text-2xl text-brand-gold mb-1"></i>
                    <p class="text-xs text-gray-500">Click or drag image to upload</p>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_primary" value="1" class="text-brand-gold rounded">
                        <span>Set as Primary</span>
                    </label>
                    <button type="submit" class="bg-brand-gold text-brand-wine px-3 py-1.5 rounded text-sm font-bold hover:bg-brand-lightgold transition-colors">
                        Upload
                    </button>
                </div>
            </form>

            <div class="grid grid-cols-2 gap-3">
                <?php foreach($images as $img): ?>
                    <div class="relative group rounded-lg overflow-hidden border <?php echo $img['is_primary'] ? 'border-brand-gold shadow-md' : 'border-gray-200'; ?>">
                        <img src="<?php echo htmlspecialchars($img['image_url']); ?>" class="w-full h-24 object-cover">
                        <?php if($img['is_primary']): ?>
                            <span class="absolute top-1 left-1 bg-brand-gold text-white text-[9px] uppercase px-1.5 py-0.5 rounded shadow">Primary</span>
                        <?php endif; ?>

                        <form method="POST" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Delete image?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete_image">
                            <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                            <button type="submit" class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>