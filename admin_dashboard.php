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

// Ensure user is admin
$authMiddleware->requireRole('admin');

// Log activity
$authMiddleware->logActivity(
    $_SESSION['user']['id'],
    'dashboard_access',
    'Admin accessed dashboard'
);

// Get CSRF token for any forms
$csrfToken = Middleware::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduClear - Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
        }
        .tab-content {
            padding: 20px 0;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
        }
        .action-buttons button {
            margin: 0 2px;
        }
    </style>
</head>
<body>
    <?php 
    // Render navigation with current page
    renderNavigation('dashboard');
    ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Dashboard</h1>
            <div>
                <button class="btn btn-outline-primary mr-2" id="refreshData">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="btn btn-primary" id="exportReport">
                    <i class="fas fa-download"></i> Export Report
                </button>
            </div>
        </div>

        <!-- Dashboard Tabs -->
        <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab">
                    <i class="fas fa-chart-line"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="payments-tab" data-toggle="tab" href="#payments" role="tab">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="students-tab" data-toggle="tab" href="#students" role="tab">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dashboardTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Payments</h6>
                                        <h3 class="mb-0" id="totalPayments">$0</h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Other stat cards... -->
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Payment Trends</h5>
                                <div style="height: 300px">
                                    <canvas id="paymentTrends"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Clearance Status</h5>
                                <div style="height: 300px">
                                    <canvas id="clearanceStatus"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card dashboard-card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Recent Payments</h5>
                            <div class="input-group w-25">
                                <input type="text" class="form-control" id="searchPayments" placeholder="Search payments...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Student Name</th>
                                        <th>Amount</th>
                                        <th>Payment Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentRecords">
                                    <!-- Payment records will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other tabs content... -->
        </div>
    </div>

    <!-- Add CSRF token to window object for AJAX requests -->
    <script>
        window.csrfToken = '<?= $csrfToken ?>';
    </script>
    
    <!-- Load dashboard JavaScript -->
    <script src="js/dashboard.js"></script>
</body>
</html>
