<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
require_once __DIR__ . '/../assets/components/ui.php';

include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$user_id = $_SESSION['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($first_name && $last_name && $phone) {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $phone, $user_id]);
        $success = "Profile updated successfully.";
    } else {
        $error = "Please fill all required fields.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<main class="flex-1 overflow-y-auto bg-brand-cultured p-6 md:p-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-brand-wine">Profile Details</h1>
        <p class="text-brand-wine/70 mt-1 font-light">Manage your personal information and preferences.</p>
    </div>

    <div class="max-w-3xl">
        <?php if (isset($success)): ?>
            <?php echo UI::alert($success, 'success'); ?>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <?php echo UI::alert($error, 'error'); ?>
        <?php endif; ?>

        <div class="bg-white rounded-3xl shadow-sm border border-brand-gold/20 overflow-hidden">
            <div class="p-8">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="flex items-center space-x-6 mb-8 border-b border-gray-100 pb-8">
                        <div class="w-24 h-24 rounded-full bg-brand-wine text-brand-gold flex items-center justify-center text-3xl font-bold shadow-md">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-brand-wine"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p class="text-brand-wine/60 text-sm mt-1"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php echo UI::input('first_name', 'First Name', 'text', $user['first_name']); ?>
                        <?php echo UI::input('last_name', 'Last Name', 'text', $user['last_name']); ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email is read-only here as changing it typically requires reverification -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-brand-wine mb-2">Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                                   class="w-full bg-gray-100 border border-gray-200 rounded-xl py-3 px-4 text-gray-500 outline-none cursor-not-allowed">
                            <p class="text-xs text-brand-wine/50 mt-1 font-light">Email cannot be changed directly. Contact support.</p>
                        </div>
                        <?php echo UI::input('phone', 'Phone Number', 'tel', $user['phone']); ?>
                    </div>

                    <div class="pt-6 border-t border-brand-gold/20">
                        <?php echo UI::button('Save Changes', 'submit', 'w-full md:w-auto'); ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>