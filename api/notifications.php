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

try {
    $userId = $auth->getCurrentUser()['id'];
    
    // Get unread notifications for the user
    $stmt = $db->prepare('
        SELECT n.*, nt.icon 
        FROM notifications n
        JOIN notification_types nt ON n.type_id = nt.id
        WHERE n.user_id = ? AND n.read_at IS NULL
        ORDER BY n.created_at DESC
        LIMIT 10
    ');
    
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format notifications for display
    $formattedNotifications = array_map(function($n) {
        return [
            'id' => $n['id'],
            'message' => $n['message'],
            'link' => $n['link'],
            'icon' => $n['icon'],
            'date' => $n['created_at']
        ];
    }, $notifications);

    echo json_encode([
        'success' => true,
        'notifications' => $formattedNotifications
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error fetching notifications'
    ]);
    error_log($e->getMessage());
}
