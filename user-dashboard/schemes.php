<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
require_once __DIR__ . '/../classes/SavingsScheme.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$engine = new SavingsScheme($pdo);

// Handle Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $enroll_id = filter_input(INPUT_POST, 'enrollment_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if ($enroll_id && $amount) {
        $result = $engine->processInstallment($enroll_id, $amount, 'online');
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Fetch User's Enrollments
$query = "
    SELECT e.*, s.name, s.monthly_amount, s.duration_months,
           (SELECT COUNT(*) FROM scheme_installments WHERE enrollment_id = e.id AND status = 'paid') as paid_months
    FROM scheme_enrollments e
    JOIN savings_schemes s ON e.scheme_id = s.id
    WHERE e.user_id = ?
    ORDER BY e.status ASC, e.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$enrollments = $stmt->fetchAll();

// Fetch Today's Rate for UI Display
$today = date('Y-m-d');
$rateStmt = $pdo->prepare("SELECT rate_per_gram FROM scheme_gold_rates WHERE date = ? AND metal_type = 'gold' AND purity = '22K'");
$rateStmt->execute([$today]);
$todayRate = $rateStmt->fetchColumn();
?>

<main class="flex-1 overflow-y-auto bg-brand-cultured p-6 md:p-10">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-brand-wine">My Savings Schemes</h1>
            <p class="text-brand-wine/70 mt-1 font-light">Track your gold reservations and pay upcoming installments.</p>
        </div>
        <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-brand-gold/30">
            <span class="text-xs text-gray-500 block">Today's 22K Gold Rate</span>
            <span class="font-bold text-brand-wine">₹<?php echo number_format($todayRate ?: 0, 2); ?>/g</span>
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

    <div class="grid grid-cols-1 gap-8">
        <?php if (empty($enrollments)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-brand-gold/20 shadow-sm">
                <div class="w-20 h-20 bg-brand-cultured rounded-full flex items-center justify-center mx-auto mb-6 text-brand-gold/50">
                    <i class="fas fa-piggy-bank text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-wine mb-2">No Active Schemes</h3>
                <p class="text-brand-wine/70 mb-6 font-light">Start investing in gold today with our flexible savings plans.</p>
                <a href="/savings-schemes/enroll.php" class="inline-block bg-brand-wine text-white px-8 py-3 rounded-full font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                    Explore Schemes
                </a>
            </div>
        <?php else: foreach ($enrollments as $e):
            $progress = ($e['paid_months'] / $e['duration_months']) * 100;
        ?>
            <div class="bg-white rounded-[2rem] shadow-sm border border-brand-gold/20 overflow-hidden flex flex-col lg:flex-row">
                <!-- Info Section -->
                <div class="p-8 lg:w-2/3 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-2xl font-bold text-brand-wine"><?php echo htmlspecialchars($e['name']); ?></h2>
                                <p class="text-sm text-gray-500 font-light mt-1">Started on <?php echo date('M j, Y', strtotime($e['start_date'])); ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?php
                                echo $e['status'] === 'active' ? 'bg-blue-100 text-blue-700' :
                                    ($e['status'] === 'matured' ? 'bg-green-100 text-green-700' :
                                    ($e['status'] === 'redeemed' ? 'bg-gray-100 text-gray-700' : 'bg-red-100 text-red-700'));
                            ?>">
                                <?php echo htmlspecialchars($e['status']); ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Paid / Total</p>
                                <p class="font-bold text-brand-wine mt-1">₹<?php echo number_format($e['total_paid']); ?> / ₹<?php echo number_format($e['monthly_amount'] * $e['duration_months']); ?></p>
                            </div>
                            <div class="bg-brand-gold/10 p-4 rounded-xl border border-brand-gold/20">
                                <p class="text-xs text-brand-wine/60 uppercase tracking-wide">Gold Reserved</p>
                                <p class="font-bold text-brand-gold mt-1 text-lg"><?php echo number_format($e['total_gold_reserved_grams'], 4); ?>g</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Est. Value Today</p>
                                <p class="font-bold text-gray-900 mt-1">₹<?php echo number_format($e['total_gold_reserved_grams'] * $todayRate, 2); ?></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Progress</p>
                                <p class="font-bold text-brand-wine mt-1"><?php echo $e['paid_months']; ?> of <?php echo $e['duration_months']; ?> Months</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2">
                            <div class="bg-brand-gold h-2.5 rounded-full transition-all duration-1000" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                </div>

                <!-- Action Section -->
                <div class="bg-brand-wine/5 p-8 lg:w-1/3 border-t lg:border-t-0 lg:border-l border-brand-gold/10 flex flex-col justify-center items-center text-center">
                    <?php if ($e['status'] === 'active'): ?>
                        <p class="text-sm font-medium text-brand-wine mb-2">Next Installment Due</p>
                        <p class="text-3xl font-bold text-brand-wine mb-6">₹<?php echo number_format($e['monthly_amount'], 2); ?></p>

                        <form method="POST" class="w-full" onsubmit="return confirm('Pay installment of ₹<?php echo number_format($e['monthly_amount']); ?>? Gold will be reserved at today\'s rate of ₹<?php echo number_format($todayRate); ?>/g.');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="pay">
                            <input type="hidden" name="enrollment_id" value="<?php echo $e['id']; ?>">
                            <input type="hidden" name="amount" value="<?php echo $e['monthly_amount']; ?>">

                            <button type="submit" class="w-full bg-brand-gold text-brand-wine py-3 rounded-xl font-bold shadow-md hover:bg-brand-lightgold transition-all" <?php echo !$todayRate ? 'disabled' : ''; ?>>
                                <?php echo $todayRate ? 'Pay Installment Now' : 'Rate Not Available'; ?>
                            </button>
                        </form>
                    <?php elseif ($e['status'] === 'matured'): ?>
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4 mx-auto">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-brand-wine mb-4">Scheme Matured</p>
                        <a href="/user-dashboard/redemption.php?id=<?php echo $e['id']; ?>" class="w-full block bg-brand-wine text-white py-3 rounded-xl font-medium shadow-md hover:bg-brand-burgundy transition-all">
                            Redeem Now
                        </a>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">This scheme is <?php echo htmlspecialchars($e['status']); ?>.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>