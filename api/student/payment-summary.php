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
    
    // Get student's payment summary
    $stmt = $db->prepare('
        SELECT 
            s.total_fees as total_due,
            COALESCE(SUM(p.amount), 0) as paid_amount
        FROM students s
        LEFT JOIN payments p ON s.id = p.student_id AND p.status = "completed"
        WHERE s.user_id = ?
        GROUP BY s.id
    ');
    
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $totalDue = floatval($result['total_due']);
        $paidAmount = floatval($result['paid_amount']);
        $balance = $totalDue - $paidAmount;
        
        echo json_encode([
            'success' => true,
            'totalDue' => $totalDue,
            'paidAmount' => $paidAmount,
            'balance' => $balance
        ]);
    } else {
        throw new Exception('Payment information not found');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error fetching payment summary'
    ]);
    error_log($e->getMessage());
}
