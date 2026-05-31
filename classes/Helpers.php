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