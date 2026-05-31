<?php

class Helpers {
    /**
     * Verifies the CSRF token and terminates the request if invalid.
     */
    public static function verifyCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die("CSRF token validation failed.");
        }
    }
}

/**
 * Reusable Notification System
 */
class Notifier {

    /**
     * Send an email notification
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $templateName Name of the template file
     * @param array $data Data to bind to template
     * @return bool
     */
    public static function sendEmail($to, $subject, $templateName, $data = []) {
        // Prepare HTML content from template
        // $htmlContent = self::renderTemplate($templateName, $data);

        // In production, integrate with a service like SendGrid, AWS SES, or use PHPMailer
        // mail($to, $subject, $htmlContent, "Content-Type: text/html; charset=UTF-8");

        // Simulating success
        return true;
    }

    /**
     * Send an SMS notification
     *
     * @param string $phone Recipient phone number
     * @param string $message Text message content
     * @return bool
     */
    public static function sendSms($phone, $message) {
        // In production, integrate with Twilio, MSG91, or AWS SNS

        // Simulating success
        return true;
    }

    /**
     * Helper to send Order Placed notifications
     */
    public static function notifyOrderPlaced($user, $order) {
        self::sendEmail($user['email'], "Order Confirmed - #" . $order['id'], 'order_placed', ['order' => $order]);
        self::sendSms($user['phone'], "Thank you for your purchase! Your order #" . $order['id'] . " has been placed.");
        // Notify admin
        self::sendAdminAlert("New Order Received: #" . $order['id']);
    }

    /**
     * Helper to send Installment Reminders
     */
    public static function notifyInstallmentReminder($user, $scheme, $amountDue) {
        self::sendEmail($user['email'], "Installment Due - " . $scheme['name'], 'installment_reminder', ['scheme' => $scheme, 'amount' => $amountDue]);
        self::sendSms($user['phone'], "Reminder: Your installment of ₹{$amountDue} for {$scheme['name']} is due soon. Pay online to reserve gold at today's rate.");
    }

    /**
     * Helper to send Maturity Alerts
     */
    public static function notifySchemeMaturity($user, $scheme) {
        self::sendEmail($user['email'], "Congratulations! Your Scheme has Matured", 'scheme_matured', ['scheme' => $scheme]);
        self::sendSms($user['phone'], "Congrats! Your {$scheme['name']} has matured. You can now redeem your accumulated gold online.");
    }

    /**
     * Notify Admins of Low Stock
     */
    public static function notifyLowStock($productName, $sku, $currentStock) {
        self::sendAdminAlert("Low Stock Alert: {$productName} (SKU: {$sku}) is down to {$currentStock} units.");
    }

    /**
     * Central Admin Alert Helper
     */
    private static function sendAdminAlert($message) {
        // In production, this might send to a specific admin group email, a Slack webhook, or a WhatsApp group.
        // self::sendEmail('admin@narayanjewelers.com', "Admin Alert", 'admin_alert', ['message' => $message]);
        return true;
    }
}