<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/auth/Authentication.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';

use App\Auth\Authentication;
use App\Middleware\AuthMiddleware;
use App\Middleware\Middleware;

class API {
    private $db;
    private $auth;
    private $middleware;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new Authentication($this->db);
        $this->middleware = new AuthMiddleware($this->db);
        
        header('Content-Type: application/json');
        Middleware::secureHeaders();
    }

    private function response($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    private function error($message, $status = 500) {
        $this->response(['success' => false, 'error' => $message], $status);
    }

    // Admin Dashboard Endpoints
    public function getDashboardStats() {
        $this->middleware->requireRole('admin');
        
        try {
            $stats = [
                'totalPayments' => 0,
                'clearedStudents' => 0,
                'pendingPayments' => 0,
                'totalStudents' => 0
            ];

            // Get total payments
            $stmt = $this->db->query('SELECT SUM(amount) as total FROM payments');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['totalPayments'] = $result['total'] ?? 0;

            // Get cleared students
            $stmt = $this->db->query('SELECT COUNT(*) as count FROM students WHERE clearance_status = "cleared"');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['clearedStudents'] = $result['count'] ?? 0;

            // Get pending payments
            $stmt = $this->db->query('SELECT COUNT(*) as count FROM payments WHERE status = "pending"');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['pendingPayments'] = $result['count'] ?? 0;

            // Get total students
            $stmt = $this->db->query('SELECT COUNT(*) as count FROM students');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['totalStudents'] = $result['count'] ?? 0;

            $this->response(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->error('Error fetching dashboard stats');
        }
    }

    // Student Dashboard Endpoints
    public function getStudentPaymentSummary() {
        $this->middleware->requireRole('student');
        
        try {
            $userId = $_SESSION['user']['id'];
            
            $stmt = $this->db->prepare('
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
                
                $this->response([
                    'success' => true,
                    'totalDue' => $totalDue,
                    'paidAmount' => $paidAmount,
                    'balance' => $balance
                ]);
            } else {
                $this->error('Payment information not found', 404);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->error('Error fetching payment summary');
        }
    }

    public function getClearanceRequirements() {
        $this->middleware->requireRole('student');
        
        try {
            $userId = $_SESSION['user']['id'];
            
            $stmt = $this->db->prepare('
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
            
            $this->response([
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
            error_log($e->getMessage());
            $this->error('Error fetching clearance requirements');
        }
    }

    public function getNotifications() {
        $this->middleware->requireAuth();
        
        try {
            $userId = $_SESSION['user']['id'];
            
            $stmt = $this->db->prepare('
                SELECT n.*, nt.icon 
                FROM notifications n
                JOIN notification_types nt ON n.type_id = nt.id
                WHERE n.user_id = ? AND n.read_at IS NULL
                ORDER BY n.created_at DESC
                LIMIT 10
            ');
            
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->response([
                'success' => true,
                'notifications' => array_map(function($n) {
                    return [
                        'id' => $n['id'],
                        'message' => $n['message'],
                        'link' => $n['link'],
                        'icon' => $n['icon'],
                        'date' => $n['created_at']
                    ];
                }, $notifications)
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->error('Error fetching notifications');
        }
    }
}

// Handle API requests
$api = new API();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getDashboardStats':
            $api->getDashboardStats();
            break;
            
        case 'getStudentPaymentSummary':
            $api->getStudentPaymentSummary();
            break;
            
        case 'getClearanceRequirements':
            $api->getClearanceRequirements();
            break;
            
        case 'getNotifications':
            $api->getNotifications();
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invalid endpoint']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
