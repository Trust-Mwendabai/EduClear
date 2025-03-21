<?php
namespace App\Middleware;

class Middleware {
    public static function handleCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !self::validateCSRFToken($_POST['csrf_token'])) {
                http_response_code(403);
                die('Invalid CSRF token');
            }
        }
    }

    public static function generateCSRFToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private static function validateCSRFToken($token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function secureHeaders() {
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://stackpath.bootstrapcdn.com https://code.jquery.com https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://stackpath.bootstrapcdn.com https://cdnjs.cloudflare.com; img-src \'self\' data:; font-src \'self\' https://cdnjs.cloudflare.com');
    }
}
