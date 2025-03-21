<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../auth/auth.php';

// Verify JWT token
$user = validateToken();

if (!$user || $user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$db = Database::getInstance()->getConnection();

// Get student details
$stmt = $db->prepare('SELECT * FROM students WHERE user_id = ?');
$stmt->execute([$user['id']]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
    exit;
}

// Get total fees for current semester
$stmt = $db->prepare('
    SELECT amount 
    FROM fee_structure 
    WHERE program = ? AND year_of_study = ? 
    AND academic_year = ? AND semester = ?
');
$stmt->execute([
    $student['program'],
    $student['year_of_study'],
    '2024/2025', // Current academic year
    1 // Current semester
]);
$feeStructure = $stmt->fetch();
$totalFees = $feeStructure ? $feeStructure['amount'] : 0;

// Get total amount paid
$stmt = $db->prepare('
    SELECT COALESCE(SUM(amount), 0) as total_paid 
    FROM payments 
    WHERE student_id = ? AND status = "completed"
');
$stmt->execute([$student['id']]);
$result = $stmt->fetch();
$amountPaid = $result['total_paid'];

// Calculate balance
$balance = $totalFees - $amountPaid;

// Get recent payments
$stmt = $db->prepare('
    SELECT * FROM payments 
    WHERE student_id = ? 
    ORDER BY payment_date DESC 
    LIMIT 5
');
$stmt->execute([$student['id']]);
$recentPayments = $stmt->fetchAll();

// Return dashboard data
echo json_encode([
    'student' => [
        'id' => $student['id'],
        'name' => $student['first_name'] . ' ' . $student['last_name'],
        'studentNumber' => $student['student_number'],
        'program' => $student['program'],
        'yearOfStudy' => $student['year_of_study']
    ],
    'totalFees' => $totalFees,
    'amountPaid' => $amountPaid,
    'balance' => $balance,
    'recentPayments' => $recentPayments
]);
