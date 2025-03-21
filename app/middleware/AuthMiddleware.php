<?php
namespace App\Middleware;

use App\Auth\Authentication;

class AuthMiddleware {
    private $auth;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new Authentication($db);
    }

    public function requireAuth() {
        if (!$this->auth->isLoggedIn()) {
            // Store the intended URL for redirect after login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /login.php');
            exit;
        }

        // Check session timeout
        $this->auth->checkSessionTimeout();

        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['last_regeneration']) || 
            (time() - $_SESSION['last_regeneration']) > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    public function requireRole($role) {
        $this->requireAuth();
        
        if ($_SESSION['user']['role'] !== $role) {
            header('Location: /unauthorized.php');
            exit;
        }
    }

    public function requirePermission($permission) {
        $this->requireAuth();
        
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as has_permission 
            FROM user_permissions up 
            JOIN permissions p ON up.permission_id = p.id 
            WHERE up.user_id = ? AND p.name = ?
        ');
        
        $stmt->execute([$_SESSION['user']['id'], $permission]);
        $result = $stmt->fetch();
        
        if (!$result['has_permission']) {
            header('Location: /unauthorized.php');
            exit;
        }
    }

    public function logActivity($userId, $action, $details = null) {
        $stmt = $this->db->prepare('
            INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    }
}
