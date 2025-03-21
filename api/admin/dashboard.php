<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../auth/auth.php';

// Verify JWT token
$user = validateToken();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$db = Database::getInstance()->getConnection();

// Get total number of students
$stmt = $db->query('SELECT COUNT(*) as total FROM students');
$totalStudents = $stmt->fetch()['total'];

// Get total payments
$stmt = $db->query('
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE status = "completed"
');
$totalPayments = $stmt->fetch()['total'];

// Get pending clearance requests
$stmt = $db->query('
    SELECT COUNT(*) as total 
    FROM clearance 
    WHERE status = "pending"
');
$pendingClearance = $stmt->fetch()['total'];

// Get number of active users
$stmt = $db->query('SELECT COUNT(*) as total FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 24 HOUR)');
$activeUsers = $stmt->fetch()['total'];

// Get recent activity
$stmt = $db->query('
    SELECT 
        a.timestamp,
        a.action,
        a.status,
        CONCAT(s.first_name, " ", s.last_name) as student_name
    FROM (
        -- Payment activities
        SELECT 
            payment_date as timestamp,
            "Payment" as action,
            status,
            student_id
        FROM payments
        UNION ALL
        -- Clearance activities
        SELECT 
            created_at as timestamp,
            "Clearance Request" as action,
            status,
            student_id
        FROM clearance
    ) a
    JOIN students s ON a.student_id = s.id
    ORDER BY a.timestamp DESC
    LIMIT 10
');
$recentActivity = $stmt->fetchAll();

// Return dashboard data
echo json_encode([
    'totalStudents' => $totalStudents,
    'totalPayments' => $totalPayments,
    'pendingClearance' => $pendingClearance,
    'activeUsers' => $activeUsers,
    'recentActivity' => $recentActivity
]);
