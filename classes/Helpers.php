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
    }
}