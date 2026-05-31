<?php
class SavingsScheme {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Process an installment payment and reserve gold at today's rate
     *
     * @param int $enrollment_id
     * @param float $amount
     * @param string $payment_method
     * @return array ['success' => bool, 'message' => string]
     */
    public function processInstallment($enrollment_id, $amount, $payment_method) {
        try {
            $this->pdo->beginTransaction();

            // 1. Verify enrollment is active
            $stmt = $this->pdo->prepare("SELECT e.id, e.user_id, s.monthly_amount, s.duration_months FROM scheme_enrollments e JOIN savings_schemes s ON e.scheme_id = s.id WHERE e.id = ? AND e.status = 'active' FOR UPDATE");
            $stmt->execute([$enrollment_id]);
            $enrollment = $stmt->fetch();

            if (!$enrollment) {
                throw new Exception("Enrollment not found or inactive.");
            }

            if ($amount != $enrollment['monthly_amount']) {
                throw new Exception("Payment amount does not match required installment.");
            }

            // 2. Determine Installment Number
            $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM scheme_installments WHERE enrollment_id = ? AND status = 'paid'");
            $stmtCount->execute([$enrollment_id]);
            $paidCount = $stmtCount->fetchColumn();

            if ($paidCount >= $enrollment['duration_months']) {
                throw new Exception("All installments have already been paid.");
            }
            $installment_number = $paidCount + 1;

            // 3. Fetch Today's Gold Rate (22K default for schemes)
            $today = date('Y-m-d');
            $stmtRate = $this->pdo->prepare("SELECT id, rate_per_gram FROM scheme_gold_rates WHERE date = ? AND metal_type = 'gold' AND purity = '22K'");
            $stmtRate->execute([$today]);
            $rate = $stmtRate->fetch();

            if (!$rate) {
                throw new Exception("Today's live gold rate is not set. Please contact support.");
            }

            // 4. Calculate Reserved Grams
            $reserved_grams = $amount / $rate['rate_per_gram'];

            // 5. Create Installment Record
            $stmtInst = $this->pdo->prepare("INSERT INTO scheme_installments (enrollment_id, installment_number, amount_paid, payment_date, gold_rate_id, gold_reserved_grams, status) VALUES (?, ?, ?, ?, ?, ?, 'paid')");
            $stmtInst->execute([$enrollment_id, $installment_number, $amount, $today, $rate['id'], $reserved_grams]);
            $installment_id = $this->pdo->lastInsertId();

            // 6. Record Transaction
            $transaction_id = 'SCH_TXN_' . strtoupper(uniqid());
            $stmtTrans = $this->pdo->prepare("INSERT INTO scheme_transactions (installment_id, transaction_id, payment_method, amount, status) VALUES (?, ?, ?, ?, 'success')");
            $stmtTrans->execute([$installment_id, $transaction_id, $payment_method, $amount]);

            // 7. Update Enrollment Totals
            $stmtUpdate = $this->pdo->prepare("UPDATE scheme_enrollments SET total_paid = total_paid + ?, total_gold_reserved_grams = total_gold_reserved_grams + ? WHERE id = ?");
            $stmtUpdate->execute([$amount, $reserved_grams, $enrollment_id]);

            // 8. Auto-Maturity Check
            if ($installment_number == $enrollment['duration_months']) {
                $this->triggerMaturity($enrollment_id);
            }

            $this->pdo->commit();
            return ['success' => true, 'message' => "Installment paid successfully. Reserved " . number_format($reserved_grams, 4) . "g of gold."];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Trigger maturity for an enrollment
     */
    private function triggerMaturity($enrollment_id) {
        // Update status to matured
        $stmt = $this->pdo->prepare("UPDATE scheme_enrollments SET status = 'matured' WHERE id = ?");
        $stmt->execute([$enrollment_id]);

        // Log maturity
        $stmtLog = $this->pdo->prepare("
            INSERT INTO scheme_maturity_logs (enrollment_id, maturity_date, final_gold_grams)
            SELECT id, CURDATE(), total_gold_reserved_grams FROM scheme_enrollments WHERE id = ?
        ");
        $stmtLog->execute([$enrollment_id]);
    }
}
?>