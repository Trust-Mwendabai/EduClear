<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/auth/Authentication.php';
require_once __DIR__ . '/../../app/middleware/AuthMiddleware.php';

use App\Auth\Authentication;
use App\Middleware\AuthMiddleware;
use App\Middleware\Middleware;

header('Content-Type: application/json');
Middleware::secureHeaders();

$db = Database::getInstance()->getConnection();
$authMiddleware = new AuthMiddleware($db);

// Ensure user is student
$authMiddleware->requireRole('student');

try {
    $userId = $_SESSION['user']['id'];
    
    // Get student's clearance requirements
    $stmt = $db->prepare('
        SELECT 
            r.id,
            r.name as requirement,
            r.description,
            COALESCE(cr.status, "pending") as status,
            cr.updated_at
        FROM clearance_requirements r
        LEFT JOIN clearance_requirement_status cr ON r.id = cr.requirement_id
        LEFT JOIN students s ON cr.student_id = s.id
        WHERE s.user_id = ? OR cr.student_id IS NULL
        ORDER BY r.order_index ASC
    ');
    
    $stmt->execute([$userId]);
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'requirements' => array_map(function($req) {
            return [
                'id' => $req['id'],
                'requirement' => $req['requirement'],
                'description' => $req['description'],
                'status' => $req['status'],
                'lastUpdated' => $req['updated_at']
            ];
        }, $requirements)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error fetching clearance requirements'
    ]);
    error_log($e->getMessage());
}
