<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$enrollment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$enrollment_id) {
    die("Invalid request.");
}

// Fetch scheme data
$stmt = $pdo->prepare("
    SELECT e.*, s.name, s.benefits_description,
           (SELECT final_gold_grams FROM scheme_maturity_logs WHERE enrollment_id = e.id) as final_grams
    FROM scheme_enrollments e
    JOIN savings_schemes s ON e.scheme_id = s.id
    WHERE e.id = ? AND e.user_id = ? AND e.status = 'matured'
");
$stmt->execute([$enrollment_id, $user_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    echo "<div class='p-10'>Scheme not found, or not eligible for redemption.</div>";
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

// Handle Redemption
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    // Check if there is an active cart
    $stmtCart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmtCart->execute([$user_id]);
    $cart = $stmtCart->fetch();

    if ($cart) {
        $stmtItems = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = ?");
        $stmtItems->execute([$cart['id']]);
        if ($stmtItems->fetchColumn() > 0) {
            // Apply redemption logic to checkout flow via session
            $_SESSION['pending_redemption'] = [
                'enrollment_id' => $enrollment_id,
                'gold_grams' => $enrollment['final_grams']
            ];
            header("Location: /checkout.php");
            exit;
        }
    }

    $error = "You must add items to your cart before redeeming your reserved gold.";
}

// Fetch Today's Rate for estimation
$today = date('Y-m-d');
$rateStmt = $pdo->prepare("SELECT rate_per_gram FROM scheme_gold_rates WHERE date = ? AND metal_type = 'gold' AND purity = '22K'");
$rateStmt->execute([$today]);
$todayRate = $rateStmt->fetchColumn();

$estimatedValue = $enrollment['final_grams'] * $todayRate;
?>

<main class="flex-1 overflow-y-auto bg-brand-cultured p-6 md:p-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-brand-wine">Redeem Your Savings</h1>
        <p class="text-brand-wine/70 mt-1 font-light">Congratulations! Your scheme has matured.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-6 border border-red-200">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="max-w-3xl bg-white rounded-3xl shadow-lg border border-brand-gold/20 overflow-hidden">
        <div class="bg-gradient-to-br from-brand-wine to-brand-burgundy p-8 text-center relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5 blur-3xl"></div>
            <i class="fas fa-gift text-5xl text-brand-gold mb-4 relative z-10"></i>
            <h2 class="text-2xl font-bold text-white relative z-10"><?php echo htmlspecialchars($enrollment['name']); ?></h2>
            <p class="text-white/70 font-light mt-1 relative z-10">Matured on <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div class="bg-brand-cultured/50 p-6 rounded-2xl text-center border border-brand-gold/10">
                    <p class="text-sm text-brand-wine/60 uppercase tracking-widest font-bold mb-2">Total Gold Reserved</p>
                    <p class="text-3xl font-bold text-brand-gold"><?php echo number_format($enrollment['final_grams'], 4); ?>g</p>
                </div>
                <div class="bg-brand-cultured/50 p-6 rounded-2xl text-center border border-brand-gold/10">
                    <p class="text-sm text-brand-wine/60 uppercase tracking-widest font-bold mb-2">Est. Market Value</p>
                    <p class="text-3xl font-bold text-brand-wine">₹<?php echo number_format($estimatedValue, 2); ?></p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 mb-8">
                <h4 class="font-bold text-blue-800 mb-2 flex items-center"><i class="fas fa-info-circle mr-2"></i> How to Redeem</h4>
                <ol class="list-decimal list-inside text-sm text-blue-700 space-y-2">
                    <li>Browse our catalog and add jewelry to your cart.</li>
                    <li>Return to this page or proceed directly to checkout.</li>
                    <li>Your reserved gold (<strong><?php echo number_format($enrollment['final_grams'], 4); ?>g</strong>) will be deducted from the gold weight of the item you purchase.</li>
                    <li>You only pay for the remaining metal weight, making charges, and taxes.</li>
                </ol>
            </div>

            <form method="POST" class="text-center">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" class="bg-brand-gold text-brand-wine px-10 py-4 rounded-xl font-bold shadow-lg hover:bg-brand-lightgold transition-all text-lg w-full md:w-auto">
                    Proceed to Checkout with Redemption
                </button>
            </form>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>