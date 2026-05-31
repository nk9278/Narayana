<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    $name = trim($_POST['name'] ?? '');
    $duration = filter_input(INPUT_POST, 'duration_months', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'monthly_amount', FILTER_VALIDATE_FLOAT);
    $benefits = trim($_POST['benefits_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name && $duration && $amount) {
        try {
            $stmt = $pdo->prepare("INSERT INTO savings_schemes (name, description, duration_months, monthly_amount, benefits_description, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $duration, $amount, $benefits, $is_active]);
            $success = "Savings Scheme created successfully!";
        } catch(PDOException $e) {
            $error = "Error creating scheme: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields properly.";
    }
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Savings Scheme</h1>
        <p class="text-gray-500 mt-1 text-sm">Define a new installment-based gold reservation plan.</p>
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

<form method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:p-8 max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-brand-wine mb-2">Scheme Name *</label>
            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            <p class="text-xs text-gray-500 mt-1">e.g., Swarna Mahotsav 11-Month Plan</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-brand-wine mb-2">Monthly Installment (₹) *</label>
            <input type="number" step="0.01" name="monthly_amount" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
            <p class="text-xs text-gray-500 mt-1">Fixed amount user pays every month.</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-brand-wine mb-2">Duration (Months) *</label>
            <input type="number" name="duration_months" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
        </div>
    </div>

    <div class="space-y-6 mb-8">
        <div>
            <label class="block text-sm font-bold text-brand-wine mb-2">Short Description</label>
            <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold"></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-brand-wine mb-2">Maturity Benefits / Rules</label>
            <textarea name="benefits_description" rows="4" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold"></textarea>
            <p class="text-xs text-gray-500 mt-1">Explain discounts, making charge waivers, or bonuses upon completion.</p>
        </div>

        <label class="flex items-center space-x-3 cursor-pointer">
            <input type="checkbox" name="is_active" class="w-4 h-4 text-brand-gold rounded focus:ring-brand-gold" checked>
            <span class="text-sm font-medium text-gray-700">Make this scheme Active immediately</span>
        </label>
    </div>

    <button type="submit" class="bg-brand-gold text-brand-wine px-8 py-3 rounded-lg font-bold shadow-md hover:bg-brand-lightgold transition-colors">
        Create Scheme
    </button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>