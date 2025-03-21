<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/auth/Authentication.php';
require_once __DIR__ . '/app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/components/navigation.php';

use App\Auth\Authentication;
use App\Middleware\AuthMiddleware;
use App\Middleware\Middleware;

session_start();
Middleware::secureHeaders();

$db = Database::getInstance()->getConnection();
$authMiddleware = new AuthMiddleware($db);

// Ensure user is student
$authMiddleware->requireRole('student');

// Log activity
$authMiddleware->logActivity(
    $_SESSION['user']['id'],
    'dashboard_access',
    'Student accessed dashboard'
);

// Get student data
$stmt = $db->prepare('
    SELECT s.*, 
           c.status as clearance_status,
           c.updated_at as clearance_date
    FROM students s
    LEFT JOIN clearances c ON s.id = c.student_id
    WHERE s.user_id = ?
');
$stmt->execute([$_SESSION['user']['id']]);
$studentData = $stmt->fetch(PDO::FETCH_ASSOC);

// Get CSRF token for forms
$csrfToken = Middleware::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduClear - Student Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .clearance-status {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .status-pending {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
        }
        .status-cleared {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .status-rejected {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            padding: 10px 40px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: -5px;
            top: 20px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #007bff;
        }
    </style>
</head>
<body>
    <?php renderNavigation('dashboard'); ?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Welcome, <?= htmlspecialchars($studentData['full_name']) ?></h1>
                <p class="text-muted">Student ID: <?= htmlspecialchars($studentData['student_id']) ?></p>
            </div>
            <div class="col-md-4 text-right">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Status
                </button>
            </div>
        </div>

        <!-- Clearance Status -->
        <div class="clearance-status status-<?= strtolower($studentData['clearance_status'] ?? 'pending') ?>">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4>
                        <i class="fas fa-<?= $studentData['clearance_status'] === 'Cleared' ? 'check-circle' : 'clock' ?>"></i>
                        Clearance Status: <?= htmlspecialchars($studentData['clearance_status'] ?? 'Pending') ?>
                    </h4>
                    <?php if ($studentData['clearance_date']): ?>
                        <p class="mb-0">Last Updated: <?= date('F j, Y g:i A', strtotime($studentData['clearance_date'])) ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-right">
                    <?php if ($studentData['clearance_status'] !== 'Cleared'): ?>
                        <a href="/clearance_form.php" class="btn btn-primary">
                            <i class="fas fa-file-alt"></i> Start Clearance Process
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Payment Summary -->
            <div class="col-md-4">
                <div class="card dashboard-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Payment Summary</h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Total Due:</span>
                            <span class="h4 mb-0" id="totalDue">$0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Paid Amount:</span>
                            <span class="h4 mb-0 text-success" id="paidAmount">$0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Balance:</span>
                            <span class="h4 mb-0 text-danger" id="balance">$0</span>
                        </div>
                        <div class="mt-3">
                            <a href="/make_payment.php" class="btn btn-primary btn-block">
                                <i class="fas fa-credit-card"></i> Make Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clearance Requirements -->
            <div class="col-md-8">
                <div class="card dashboard-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Clearance Requirements</h5>
                        <div class="timeline" id="clearanceTimeline">
                            <!-- Timeline items will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Recent Activity</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="activityLog">
                            <!-- Activity log will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add CSRF token to window object for AJAX requests
        window.csrfToken = '<?= $csrfToken ?>';
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/student-dashboard.js"></script>
</body>
</html>
