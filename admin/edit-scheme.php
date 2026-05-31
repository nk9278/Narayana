<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$scheme_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$scheme_id) {
    die("<div class='p-8 text-center text-red-500'>Invalid Scheme ID.</div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? 'update';

    if ($action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $benefits = trim($_POST['benefits_description'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name) {
            try {
                // Duration and amount cannot be changed if there are active enrollments,
                // but for simplicity we allow editing metadata here.
                $stmt = $pdo->prepare("UPDATE savings_schemes SET name = ?, description = ?, benefits_description = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $description, $benefits, $is_active, $scheme_id]);
                $success = "Savings Scheme updated successfully!";
            } catch(PDOException $e) {
                $error = "Error updating scheme: " . $e->getMessage();
            }
        } else {
            $error = "Scheme Name is required.";
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM savings_schemes WHERE id = ?");
            $stmt->execute([$scheme_id]);
            header("Location: /admin/schemes.php");
            exit;
        } catch(PDOException $e) {
            $error = "Cannot delete this scheme. It has existing enrollments.";
        }
    }
}

// Fetch scheme data
$stmt = $pdo->prepare("SELECT * FROM savings_schemes WHERE id = ?");
$stmt->execute([$scheme_id]);
$scheme = $stmt->fetch();

if (!$scheme) {
    die("<div class='p-8 text-center text-red-500'>Scheme not found.</div>");
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Scheme: <?php echo htmlspecialchars($scheme['name']); ?></h1>
        <p class="text-gray-500 mt-1 text-sm">Update plan rules or disable the scheme.</p>
    </div>
    <a href="/admin/schemes.php" class="text-brand-wine hover:text-brand-gold font-medium text-sm transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Back to Schemes
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
    <div class="lg:col-span-2">
        <form method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:p-8">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="update">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-brand-wine mb-2">Scheme Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($scheme['name']); ?>" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>

                <div>
                    <label class="block text-sm font-bold text-brand-wine mb-2">Monthly Installment (₹)</label>
                    <input type="number" value="<?php echo $scheme['monthly_amount']; ?>" disabled class="w-full bg-gray-100 border border-gray-300 rounded-lg py-2 px-3 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Cannot be changed after creation.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-brand-wine mb-2">Duration (Months)</label>
                    <input type="number" value="<?php echo $scheme['duration_months']; ?>" disabled class="w-full bg-gray-100 border border-gray-300 rounded-lg py-2 px-3 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Cannot be changed after creation.</p>
                </div>
            </div>

            <div class="space-y-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-brand-wine mb-2">Short Description</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold"><?php echo htmlspecialchars($scheme['description']); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-brand-wine mb-2">Maturity Benefits / Rules</label>
                    <textarea name="benefits_description" rows="4" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold"><?php echo htmlspecialchars($scheme['benefits_description']); ?></textarea>
                </div>

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_active" class="w-4 h-4 text-brand-gold rounded focus:ring-brand-gold" <?php echo $scheme['is_active'] ? 'checked' : ''; ?>>
                    <span class="text-sm font-medium text-gray-700">Scheme is Active (Visible to users)</span>
                </label>
            </div>

            <button type="submit" class="bg-brand-gold text-brand-wine px-8 py-3 rounded-lg font-bold shadow-md hover:bg-brand-lightgold transition-colors">
                Update Scheme
            </button>
        </form>
    </div>

    <div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6 lg:p-8">
            <h3 class="text-lg font-bold text-red-600 mb-4">Danger Zone</h3>
            <p class="text-sm text-gray-600 mb-6">Deleting this scheme will remove it permanently. This action cannot be undone and is only possible if there are zero user enrollments linked to it.</p>

            <form method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this scheme?');">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="delete">

                <button type="submit" class="w-full bg-white text-red-600 border border-red-600 px-4 py-3 rounded-lg font-bold shadow-sm hover:bg-red-50 hover:text-red-700 transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i>Delete Scheme
                </button>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>