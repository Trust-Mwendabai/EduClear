<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/auth/Authentication.php';
require_once __DIR__ . '/../app/middleware/Middleware.php';

use App\Auth\Authentication;
use App\Middleware\Middleware;

header('Content-Type: application/json');
Middleware::secureHeaders();

$db = Database::getInstance()->getConnection();
$auth = new Authentication($db);

// Ensure user is authenticated
$auth->requireAuth();
$auth->checkSessionTimeout();

// Handle API requests
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getDashboardStats':
            $auth->requireRole('admin');
            $stats = getDashboardStats($db);
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        case 'getPaymentHistory':
            $stats = getPaymentHistory($db);
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        case 'getStudentStatus':
            $stats = getStudentStatus($db);
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    error_log($e->getMessage());
}

function getDashboardStats($db) {
    $stats = [
        'totalPayments' => 0,
        'clearedStudents' => 0,
        'pendingPayments' => 0,
        'totalStudents' => 0
    ];

    // Get total payments
    $stmt = $db->query('SELECT SUM(amount) as total FROM payments');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['totalPayments'] = $result['total'] ?? 0;

    // Get cleared students
    $stmt = $db->query('SELECT COUNT(*) as count FROM students WHERE clearance_status = "cleared"');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['clearedStudents'] = $result['count'] ?? 0;

    // Get pending payments
    $stmt = $db->query('SELECT COUNT(*) as count FROM payments WHERE status = "pending"');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pendingPayments'] = $result['count'] ?? 0;

    // Get total students
    $stmt = $db->query('SELECT COUNT(*) as count FROM students');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['totalStudents'] = $result['count'] ?? 0;

    return $stats;
}

function getPaymentHistory($db) {
    $stmt = $db->prepare('
        SELECT p.*, s.full_name 
        FROM payments p 
        JOIN students s ON p.student_id = s.id 
        ORDER BY p.payment_date DESC 
        LIMIT 10
    ');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStudentStatus($db) {
    $stmt = $db->prepare('
        SELECT clearance_status, COUNT(*) as count 
        FROM students 
        GROUP BY clearance_status
    ');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
