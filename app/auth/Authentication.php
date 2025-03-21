<?php
namespace App\Auth;

use PDO;
use Exception;

class Authentication {
    private $db;
    private $user = null;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->initSession();
    }

    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(string $email, string $password): bool {
        try {
            $stmt = $this->db->prepare('SELECT id, email, password, role, full_name FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Remove sensitive data before storing in session
                unset($user['password']);
                $_SESSION['user'] = $user;
                $_SESSION['last_activity'] = time();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function register(array $userData): bool {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (email, password, role, full_name, created_at) 
                VALUES (?, ?, ?, ?, NOW())'
            );
            
            return $stmt->execute([
                $userData['email'],
                password_hash($userData['password'], PASSWORD_DEFAULT),
                $userData['role'],
                $userData['full_name']
            ]);
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function logout(): void {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['user']);
    }

    public function getCurrentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public function requireAuth(): void {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    public function requireRole(string $role): void {
        $this->requireAuth();
        if ($_SESSION['user']['role'] !== $role) {
            header('Location: /unauthorized.php');
            exit;
        }
    }

    public function checkSessionTimeout(): void {
        $timeout = 30 * 60; // 30 minutes
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            $this->logout();
            header('Location: /login.php?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}
