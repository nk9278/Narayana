<?php
/**
 * Cron Job: Daily Sync
 * Intended to be run daily at 00:01 AM via server cron or AWS CloudWatch Events
 * Example crontab: 1 0 * * * /usr/bin/php /path/to/project/cron/sync_rates.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php'; // Includes Notifier

echo "Starting Daily Sync Job... [" . date('Y-m-d H:i:s') . "]\n";

try {
    $pdo->beginTransaction();

    // ---------------------------------------------------------
    // 1. Sync Live Gold Rates
    // ---------------------------------------------------------
    echo "1. Syncing live metal rates...\n";
    // Mocking an API call to a market data provider (e.g., Metals-API)
    $mockApiResponse = [
        ['metal' => 'gold', 'purity' => '24K', 'rate' => rand(7000, 7500)],
        ['metal' => 'gold', 'purity' => '22K', 'rate' => rand(6500, 6900)],
        ['metal' => 'gold', 'purity' => '18K', 'rate' => rand(5000, 5500)],
        ['metal' => 'silver', 'purity' => '999', 'rate' => rand(80, 95)],
    ];

    $today = date('Y-m-d');
    $stmtRate = $pdo->prepare("
        INSERT INTO scheme_gold_rates (metal_type, purity, rate_per_gram, date)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE rate_per_gram = VALUES(rate_per_gram)
    ");

    foreach ($mockApiResponse as $rateData) {
        $stmtRate->execute([$rateData['metal'], $rateData['purity'], $rateData['rate'], $today]);
    }
    echo "   Rates synced.\n";

    // ---------------------------------------------------------
    // 2. Installment Reminders
    // ---------------------------------------------------------
    echo "2. Checking for upcoming installments...\n";
    // Logic: Find enrollments where the day of the month matches the start_date's day,
    // and it's not fully matured, and no paid installment exists for this month.

    // For demonstration, we simply query active enrollments to simulate the notification
    $stmtActive = $pdo->query("SELECT e.id, e.user_id, u.email, u.phone, s.name as scheme_name, s.monthly_amount
                               FROM scheme_enrollments e
                               JOIN users u ON e.user_id = u.id
                               JOIN savings_schemes s ON e.scheme_id = s.id
                               WHERE e.status = 'active'");

    $remindersSent = 0;
    while ($row = $stmtActive->fetch()) {
        // Mocking the date check logic here
        // If due in 3 days:
        // Notifier::sendEmail($row['email'], "Installment Due Soon", "installment_reminder", $row);
        $remindersSent++;
    }
    echo "   Reminders processed: {$remindersSent}\n";

    $pdo->commit();
    echo "Daily Sync Job Completed Successfully. [" . date('Y-m-d H:i:s') . "]\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error running cron job: " . $e->getMessage() . "\n";
    exit(1);
}
