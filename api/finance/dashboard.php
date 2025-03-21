<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../auth/auth.php';

// Verify JWT token
$user = validateToken();

if (!$user || $user['role'] !== 'finance_officer') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$db = Database::getInstance()->getConnection();

// Get total revenue
$stmt = $db->query('
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE status = "completed"
');
$totalRevenue = $stmt->fetch()['total'];

// Get today's collections
$stmt = $db->query('
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE status = "completed" 
    AND DATE(payment_date) = CURDATE()
');
$todayCollections = $stmt->fetch()['total'];

// Get pending payments
$stmt = $db->query('
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE status = "pending"
');
$pendingPayments = $stmt->fetch()['total'];

// Calculate payment success rate
$stmt = $db->query('
    SELECT 
        COUNT(*) as total_payments,
        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_payments
    FROM payments
');
$paymentStats = $stmt->fetch();
$successRate = $paymentStats['total_payments'] > 0 
    ? round(($paymentStats['successful_payments'] / $paymentStats['total_payments']) * 100, 1)
    : 0;

// Get revenue trend for the last 7 days
$stmt = $db->query('
    SELECT 
        DATE(payment_date) as date,
        COALESCE(SUM(amount), 0) as total
    FROM payments 
    WHERE status = "completed"
    AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(payment_date)
    ORDER BY date
');
$revenueTrend = [
    'labels' => [],
    'values' => []
];
while ($row = $stmt->fetch()) {
    $revenueTrend['labels'][] = date('M d', strtotime($row['date']));
    $revenueTrend['values'][] = $row['total'];
}

// Get recent transactions
$stmt = $db->query('
    SELECT 
        p.*,
        CONCAT(s.first_name, " ", s.last_name) as student_name
    FROM payments p
    JOIN students s ON p.student_id = s.id
    ORDER BY p.payment_date DESC
    LIMIT 10
');
$recentTransactions = $stmt->fetchAll();

// Return dashboard data
echo json_encode([
    'totalRevenue' => $totalRevenue,
    'todayCollections' => $todayCollections,
    'pendingPayments' => $pendingPayments,
    'successRate' => $successRate,
    'revenueTrend' => $revenueTrend,
    'recentTransactions' => $recentTransactions
]);
