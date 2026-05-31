<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    // Using a simple key-value update loop based on the schema
    $keysToUpdate = [
        'site_title', 'contact_email', 'contact_phone', 'support_address',
        'facebook_link', 'instagram_link', 'twitter_link', 'gst_percentage'
    ];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($keysToUpdate as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([$key, $_POST[$key]]);
            }
        }

        $pdo->commit();
        $success = "Settings updated successfully.";
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to update settings: " . $e->getMessage();
    }
}

// Fetch all settings
$settingsRows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
$settings = [];
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Global Settings</h1>
        <p class="text-gray-500 mt-1 text-sm">Manage store configuration, contact details, and social links.</p>
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

<form method="POST" class="space-y-8 max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="font-bold text-gray-800">General Information</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Title</label>
                <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'Narayan Jewelers'); ?>" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Global GST Percentage</label>
                <input type="number" step="0.1" name="gst_percentage" value="<?php echo htmlspecialchars($settings['gst_percentage'] ?? '3.0'); ?>" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="font-bold text-gray-800">Contact Details</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Store / Support Address</label>
                <textarea name="support_address" rows="3" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold"><?php echo htmlspecialchars($settings['support_address'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="font-bold text-gray-800">Social Links</h2>
        </div>
        <div class="p-6 grid grid-cols-1 gap-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded-l-lg border border-r-0 border-gray-300 text-blue-600">
                    <i class="fab fa-facebook-f"></i>
                </div>
                <input type="url" name="facebook_link" value="<?php echo htmlspecialchars($settings['facebook_link'] ?? ''); ?>" placeholder="https://facebook.com/yourpage" class="flex-grow border border-gray-300 rounded-r-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            </div>
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded-l-lg border border-r-0 border-gray-300 text-pink-600">
                    <i class="fab fa-instagram"></i>
                </div>
                <input type="url" name="instagram_link" value="<?php echo htmlspecialchars($settings['instagram_link'] ?? ''); ?>" placeholder="https://instagram.com/yourpage" class="flex-grow border border-gray-300 rounded-r-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            </div>
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded-l-lg border border-r-0 border-gray-300 text-blue-400">
                    <i class="fab fa-twitter"></i>
                </div>
                <input type="url" name="twitter_link" value="<?php echo htmlspecialchars($settings['twitter_link'] ?? ''); ?>" placeholder="https://twitter.com/yourpage" class="flex-grow border border-gray-300 rounded-r-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            </div>
        </div>
    </div>

    <button type="submit" class="bg-brand-gold text-brand-wine px-8 py-3 rounded-lg font-bold shadow-md hover:bg-brand-lightgold transition-colors">
        Save All Settings
    </button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>