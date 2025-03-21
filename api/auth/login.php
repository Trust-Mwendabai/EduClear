<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password are required']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

// Get database connection
$db = Database::getInstance()->getConnection();

// Check if username exists
$stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

// Generate JWT token
require_once '../../vendor/autoload.php';
use Firebase\JWT\JWT;

$key = getenv('JWT_SECRET') ?: 'your-secret-key';
$payload = [
    'user_id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'exp' => time() + (60 * 60) // Token expires in 1 hour
];

$token = JWT::encode($payload, $key, 'HS256');

// Return user data and token
echo json_encode([
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);
