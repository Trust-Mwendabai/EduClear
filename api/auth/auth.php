<?php
require_once '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validateToken() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No token provided']);
        exit;
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    
    try {
        $key = getenv('JWT_SECRET') ?: 'your-secret-key';
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        
        // Get user from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$decoded->user_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }
}
