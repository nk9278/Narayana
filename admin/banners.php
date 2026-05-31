<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $link_url = trim($_POST['link_url'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (in_array($fileExtension, $allowedExtensions) && in_array($mimeType, $allowedMimeTypes)) {
                $uploadDir = __DIR__ . '/../uploads/cms/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $safeFileName = time() . '_banner_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
                $targetFile = $uploadDir . $safeFileName;

                if (move_uploaded_file($fileTmpPath, $targetFile)) {
                    $imageUrl = '/uploads/cms/' . $safeFileName;
                    $stmt = $pdo->prepare("INSERT INTO banners (title, image_url, link_url, is_active) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $imageUrl, $link_url, $is_active]);
                    $success = "Banner added successfully.";
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, WEBP allowed.";
            }
        } else {
            $error = "Image is required.";
        }
    } elseif ($action === 'delete') {
        $banner_id = filter_input(INPUT_POST, 'banner_id', FILTER_VALIDATE_INT);
        if ($banner_id) {
            $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$banner_id]);
            $success = "Banner deleted.";
        }
    }
}

$banners = $pdo->query("SELECT * FROM banners ORDER BY position ASC, created_at DESC")->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Homepage Banners</h1>
        <p class="text-gray-500 mt-1 text-sm">Manage the rotating promotional banners on the storefront.</p>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-bold text-gray-800">Active Banners</h2>
            </div>
            <div class="p-6 space-y-6">
                <?php if(empty($banners)): ?>
                    <p class="text-center text-gray-500">No banners uploaded yet.</p>
                <?php else: foreach($banners as $banner): ?>
                    <div class="border border-gray-200 rounded-xl overflow-hidden flex flex-col md:flex-row group relative <?php echo !$banner['is_active'] ? 'opacity-50' : ''; ?>">
                        <div class="w-full md:w-48 h-32 bg-gray-100 flex-shrink-0">
                            <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 flex flex-col justify-center flex-grow">
                            <h3 class="font-bold text-brand-wine"><?php echo htmlspecialchars($banner['title']); ?></h3>
                            <p class="text-sm text-blue-500 truncate mt-1">Link: <?php echo htmlspecialchars($banner['link_url'] ?: 'None'); ?></p>
                            <span class="text-xs mt-2 <?php echo $banner['is_active'] ? 'text-green-600' : 'text-red-600'; ?> font-bold">
                                <?php echo $banner['is_active'] ? 'VISIBLE' : 'HIDDEN'; ?>
                            </span>
                        </div>

                        <form method="POST" class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Delete this banner?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                            <button type="submit" class="w-8 h-8 bg-white border border-red-200 text-red-500 rounded-lg flex items-center justify-center shadow-sm hover:bg-red-50">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h2 class="font-bold text-gray-800 mb-4">Add New Banner</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="add">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Title</label>
                    <input type="text" name="title" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link URL (Optional)</label>
                    <input type="text" name="link_url" placeholder="e.g. /category.php?c=rings" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image (JPG/PNG/WEBP)</label>
                    <input type="file" name="image" required accept="image/jpeg, image/png, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-gold/10 file:text-brand-wine hover:file:bg-brand-gold/20 cursor-pointer">
                </div>

                <label class="flex items-center space-x-2 cursor-pointer pt-2 pb-2">
                    <input type="checkbox" name="is_active" class="w-4 h-4 text-brand-gold rounded focus:ring-brand-gold" checked>
                    <span class="text-sm font-medium text-gray-700">Set as Active</span>
                </label>

                <button type="submit" class="w-full bg-brand-wine text-white py-2.5 rounded-lg font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                    Upload Banner
                </button>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>