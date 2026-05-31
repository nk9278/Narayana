<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
require_once __DIR__ . '/../assets/components/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;

// Handle Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_scheme_id']) && $user_id) {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $scheme_id = filter_input(INPUT_POST, 'enroll_scheme_id', FILTER_VALIDATE_INT);

    // Check if scheme is active
    $stmt = $pdo->prepare("SELECT id FROM savings_schemes WHERE id = ? AND is_active = 1");
    $stmt->execute([$scheme_id]);
    if ($stmt->fetch()) {
        try {
            $pdo->beginTransaction();
            $stmtEnroll = $pdo->prepare("INSERT INTO scheme_enrollments (user_id, scheme_id, start_date, status) VALUES (?, ?, CURDATE(), 'active')");
            $stmtEnroll->execute([$user_id, $scheme_id]);
            $enrollment_id = $pdo->lastInsertId();
            $pdo->commit();

            header("Location: /user-dashboard/schemes.php?enrolled=success");
            exit;
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to enroll: " . $e->getMessage();
        }
    } else {
        $error = "Invalid or inactive scheme selected.";
    }
}

// Fetch active schemes
$query = "SELECT * FROM savings_schemes WHERE is_active = 1 ORDER BY duration_months ASC";
$schemes = $pdo->query($query)->fetchAll();

include_once __DIR__ . '/../assets/includes/header.php';
?>

<main class="bg-brand-cultured min-h-screen pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-12 md:mb-16 text-center reveal active">
            <span class="inline-block py-1 px-3 rounded-full bg-brand-gold/20 text-brand-wine text-xs font-bold tracking-wider uppercase mb-4 border border-brand-gold/30">
                Fintech Jewelry Savings
            </span>
            <h1 class="text-3xl md:text-5xl font-bold text-brand-wine mb-4">Gold Savings Schemes</h1>
            <p class="text-brand-wine/70 max-w-2xl mx-auto font-light leading-relaxed">
                Invest smartly by reserving gold at live market rates through convenient monthly installments. Earn exclusive maturity benefits and discounts on your next masterpiece.
            </p>
        </div>

        <?php if (isset($error)): ?>
            <?php echo UI::alert($error, 'error'); ?>
        <?php endif; ?>

        <?php if (empty($schemes)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-brand-gold/20 shadow-sm reveal active">
                <div class="w-20 h-20 bg-brand-cultured rounded-full flex items-center justify-center mx-auto mb-6 text-brand-gold/50">
                    <i class="fas fa-piggy-bank text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-wine mb-2">No Active Schemes</h3>
                <p class="text-brand-wine/70 font-light">Check back later for new savings opportunities.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($schemes as $index => $scheme): ?>
                    <?php $delay = ($index % 3) * 100; ?>
                    <div class="bg-white rounded-[2rem] overflow-hidden floating-card border border-brand-gold/20 shadow-md flex flex-col reveal active" style="transition-delay: <?php echo $delay; ?>ms;">

                        <div class="bg-gradient-to-br from-brand-wine to-brand-burgundy p-6 relative overflow-hidden">
                            <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full bg-white/5 blur-2xl"></div>
                            <div class="absolute bottom-0 right-0 p-4 opacity-10">
                                <i class="fas fa-coins text-6xl"></i>
                            </div>

                            <h3 class="text-xl font-bold text-brand-gold mb-1 relative z-10"><?php echo htmlspecialchars($scheme['name']); ?></h3>
                            <p class="text-white/80 text-sm font-light relative z-10"><?php echo $scheme['duration_months']; ?> Month Plan</p>

                            <div class="mt-6 flex items-end justify-between relative z-10">
                                <div>
                                    <p class="text-white/60 text-xs uppercase tracking-wider mb-1">Monthly Installment</p>
                                    <p class="text-2xl font-bold text-white">₹<?php echo number_format($scheme['monthly_amount']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 flex-grow flex flex-col">
                            <?php if (!empty($scheme['description'])): ?>
                                <p class="text-brand-wine/70 text-sm mb-4 font-light italic">
                                    "<?php echo htmlspecialchars($scheme['description']); ?>"
                                </p>
                            <?php endif; ?>

                            <div class="bg-brand-cultured/50 rounded-xl p-4 mb-6 border border-brand-gold/10">
                                <h4 class="text-xs font-bold text-brand-wine uppercase tracking-wider mb-2">Maturity Benefits</h4>
                                <p class="text-sm text-brand-wine/80"><?php echo nl2br(htmlspecialchars($scheme['benefits_description'])); ?></p>
                            </div>

                            <div class="mt-auto pt-4 border-t border-gray-100">
                                <?php if ($user_id): ?>
                                    <button onclick="document.getElementById('enroll-modal-<?php echo $scheme['id']; ?>').classList.remove('hidden')" class="w-full bg-brand-gold text-brand-wine py-3.5 rounded-xl font-bold shadow-lg hover:bg-brand-lightgold hover:-translate-y-0.5 transition-all duration-300">
                                        Enroll Now
                                    </button>
                                <?php else: ?>
                                    <a href="/login.php" class="block text-center w-full bg-brand-wine text-white py-3.5 rounded-xl font-medium shadow-lg hover:bg-brand-burgundy transition-all duration-300">
                                        Login to Enroll
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <?php
                    // Render Modal for each scheme using UI component
                    if ($user_id) {
                        $modalId = 'enroll-modal-' . $scheme['id'];
                        $modalTitle = "Enroll in " . htmlspecialchars($scheme['name']);

                        $modalContent = "
                            <p class='text-sm text-gray-600 mb-4'>By enrolling, you agree to pay a fixed installment of <strong>₹" . number_format($scheme['monthly_amount']) . "</strong> every month for exactly <strong>" . $scheme['duration_months'] . " months</strong>.</p>
                            <p class='text-sm text-gray-600 mb-6'>Each installment will automatically reserve physical gold based on the live market rate on the day of payment.</p>
                            <form method='POST'>
                                <input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>
                                <input type='hidden' name='enroll_scheme_id' value='" . $scheme['id'] . "'>
                                <label class='flex items-start space-x-3 mb-6 cursor-pointer'>
                                    <input type='checkbox' required class='mt-1 text-brand-gold rounded focus:ring-brand-gold'>
                                    <span class='text-xs text-gray-600'>I have read and agree to the <a href='#' class='text-brand-wine underline'>terms and conditions</a> of the savings scheme.</span>
                                </label>
                                <div class='flex space-x-3 justify-end'>
                                    <button type='button' onclick=\"document.getElementById('{$modalId}').classList.add('hidden')\" class='px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50'>Cancel</button>
                                    <button type='submit' class='px-4 py-2 bg-brand-gold text-brand-wine rounded-lg text-sm font-bold shadow hover:bg-brand-lightgold'>Confirm Enrollment</button>
                                </div>
                            </form>
                        ";

                        echo UI::modal($modalId, $modalTitle, $modalContent);
                    }
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include_once __DIR__ . '/../assets/includes/footer.php'; ?>