<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

// Handle Form Submission for Manual Rate Override
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    $metal_type = filter_input(INPUT_POST, 'metal_type', FILTER_SANITIZE_STRING);
    $purity = filter_input(INPUT_POST, 'purity', FILTER_SANITIZE_STRING);
    $rate_per_gram = filter_input(INPUT_POST, 'rate_per_gram', FILTER_VALIDATE_FLOAT);
    $date = date('Y-m-d'); // Enforce today's date for overrides

    if ($metal_type && $purity && $rate_per_gram) {
        try {
            // Upsert mechanism: if rate for today exists, update it; otherwise insert
            $stmt = $pdo->prepare("
                INSERT INTO scheme_gold_rates (metal_type, purity, rate_per_gram, date)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rate_per_gram = VALUES(rate_per_gram)
            ");
            $stmt->execute([$metal_type, $purity, $rate_per_gram, $date]);
            $success = "Rate updated successfully for today!";
        } catch(PDOException $e) {
            $error = "Error updating rate: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all rate fields properly.";
    }
}

// Fetch Current Day's Rates
$today = date('Y-m-d');
$query = "SELECT * FROM scheme_gold_rates WHERE date = ? ORDER BY metal_type, purity";
$stmt = $pdo->prepare($query);
$stmt->execute([$today]);
$todays_rates = $stmt->fetchAll();

// Fetch Historical Rates (Last 10 days)
$queryHistory = "SELECT * FROM scheme_gold_rates WHERE date < ? ORDER BY date DESC, metal_type, purity LIMIT 30";
$stmtHistory = $pdo->prepare($queryHistory);
$stmtHistory->execute([$today]);
$historical_rates = $stmtHistory->fetchAll();

?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Live Metal Rates Engine</h1>
        <p class="text-gray-500 mt-1 text-sm">Manage daily pricing used for scheme gold reservations and dynamic product pricing.</p>
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

    <!-- Today's Rates -->
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-brand-wine/5 flex justify-between items-center">
                <h2 class="font-bold text-brand-wine">Today's Rates (<?php echo date('M j, Y'); ?>)</h2>
                <span class="text-xs font-bold bg-green-100 text-green-700 px-2 py-1 rounded uppercase tracking-wider">Live</span>
            </div>

            <?php if (empty($todays_rates)): ?>
                <div class="p-8 text-center text-gray-500">
                    <p>No rates recorded for today yet. Use the override panel to set them, or wait for the cron sync.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 border-b border-gray-100">
                                <th class="px-6 py-3 font-medium">Metal</th>
                                <th class="px-6 py-3 font-medium">Purity</th>
                                <th class="px-6 py-3 font-medium text-right">Rate per Gram (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            <?php foreach ($todays_rates as $rate): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-gray-800 uppercase"><?php echo htmlspecialchars($rate['metal_type']); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($rate['purity']); ?></td>
                                <td class="px-6 py-4 text-right font-bold text-brand-wine text-lg"><?php echo number_format($rate['rate_per_gram'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Historical Data -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800">Historical Rates</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 border-b border-gray-100">
                            <th class="px-6 py-3 font-medium">Date</th>
                            <th class="px-6 py-3 font-medium">Metal</th>
                            <th class="px-6 py-3 font-medium">Purity</th>
                            <th class="px-6 py-3 font-medium text-right">Rate per Gram (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                        <?php foreach ($historical_rates as $hr): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3"><?php echo date('M j, Y', strtotime($hr['date'])); ?></td>
                            <td class="px-6 py-3 uppercase text-xs font-bold"><?php echo htmlspecialchars($hr['metal_type']); ?></td>
                            <td class="px-6 py-3 text-gray-500"><?php echo htmlspecialchars($hr['purity']); ?></td>
                            <td class="px-6 py-3 text-right font-medium">₹<?php echo number_format($hr['rate_per_gram'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Override Panel -->
    <div class="space-y-8">
        <div class="bg-white rounded-xl shadow-lg border border-brand-gold/30 p-6">
            <h2 class="font-bold text-brand-wine mb-4 border-b border-gray-100 pb-2">Manual Rate Override</h2>
            <p class="text-xs text-gray-500 mb-6">Manually setting a rate here will immediately affect new scheme enrollments, installments, and dynamic product pricing for today.</p>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Metal Type *</label>
                    <select name="metal_type" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                        <option value="gold">Gold</option>
                        <option value="silver">Silver</option>
                        <option value="platinum">Platinum</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Purity *</label>
                    <select name="purity" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                        <option value="24K">24K</option>
                        <option value="22K">22K</option>
                        <option value="18K">18K</option>
                        <option value="999">999 (Silver/Plat)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Rate per Gram (₹) *</label>
                    <input type="number" step="0.01" name="rate_per_gram" required placeholder="e.g. 7150.00" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>

                <button type="submit" class="w-full bg-brand-gold text-brand-wine py-2.5 rounded-lg font-bold shadow-md hover:bg-brand-lightgold transition-colors mt-2">
                    Update Today's Rate
                </button>
            </form>
        </div>

        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
            <h3 class="font-bold text-gray-700 text-sm mb-2"><i class="fas fa-robot text-brand-gold mr-1"></i> API Auto-Sync</h3>
            <p class="text-xs text-gray-500">The rate engine is configured to automatically fetch rates from the market API via cron job at 00:01 AM daily.</p>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>