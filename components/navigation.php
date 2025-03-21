<?php
require_once __DIR__ . '/../app/auth/Authentication.php';
use App\Auth\Authentication;

function renderNavigation($currentPage = '') {
    $auth = new Authentication($GLOBALS['db']);
    $user = $auth->getCurrentUser();
    $isAdmin = $user && $user['role'] === 'admin';
?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-graduation-cap"></i> EduClear
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="/admin_dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'payments' ? 'active' : '' ?>" href="/payments.php">
                                <i class="fas fa-money-bill-wave"></i> Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'students' ? 'active' : '' ?>" href="/students.php">
                                <i class="fas fa-user-graduate"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>" href="/reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="/dashboard.php">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'clearance' ? 'active' : '' ?>" href="/clearance.php">
                                <i class="fas fa-check-circle"></i> Clearance Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'payments' ? 'active' : '' ?>" href="/my_payments.php">
                                <i class="fas fa-receipt"></i> My Payments
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge badge-warning" id="notificationCount">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" id="notificationMenu">
                            <div class="dropdown-item text-center text-muted">No new notifications</div>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['full_name']) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="/profile.php">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a class="dropdown-item" href="/settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script>
    // Fetch notifications
    async function fetchNotifications() {
        try {
            const response = await fetch('/api/notifications.php');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            const notificationCount = document.getElementById('notificationCount');
            const notificationMenu = document.getElementById('notificationMenu');
            
            if (data.notifications && data.notifications.length > 0) {
                notificationCount.textContent = data.notifications.length;
                notificationMenu.innerHTML = data.notifications
                    .map(n => `
                        <a class="dropdown-item" href="${n.link}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-${n.icon} mr-2"></i>
                                <div>
                                    <small class="text-muted">${new Date(n.date).toLocaleDateString()}</small>
                                    <div>${n.message}</div>
                                </div>
                            </div>
                        </a>
                    `)
                    .join('');
            } else {
                notificationCount.textContent = '0';
                notificationMenu.innerHTML = '<div class="dropdown-item text-center text-muted">No new notifications</div>';
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    // Fetch notifications on page load and every minute
    document.addEventListener('DOMContentLoaded', fetchNotifications);
    setInterval(fetchNotifications, 60000);
    </script>
<?php
}
?>
