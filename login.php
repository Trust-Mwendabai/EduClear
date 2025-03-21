<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/auth/Authentication.php';
require_once __DIR__ . '/app/middleware/Middleware.php';

use App\Auth\Authentication;
use App\Middleware\Middleware;

session_start();
Middleware::secureHeaders();

$db = Database::getInstance()->getConnection();
$auth = new Authentication($db);

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $role = $_SESSION['user']['role'];
    header('Location: ' . ($role === 'admin' ? '/admin_dashboard.php' : '/student_dashboard.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = Middleware::sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !Middleware::validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception('Invalid request');
        }

        // Validate email
        if (!Middleware::validateEmail($email)) {
            throw new Exception('Invalid email format');
        }

        // Rate limiting check
        if (!checkLoginAttempts($db, $email)) {
            throw new Exception('Too many login attempts. Please try again later.');
        }

        // Attempt login
        if ($auth->login($email, $password)) {
            // Reset login attempts on success
            resetLoginAttempts($db, $email);
            
            $role = $_SESSION['user']['role'];
            header('Location: ' . ($role === 'admin' ? '/admin_dashboard.php' : '/student_dashboard.php'));
            exit;
        } else {
            // Record failed attempt
            recordLoginAttempt($db, $email);
            throw new Exception('Invalid credentials');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Function to check login attempts
function checkLoginAttempts($db, $email) {
    $stmt = $db->prepare('
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ');
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['attempts'] < 5; // Allow 5 attempts per 15 minutes
}

// Function to record failed login attempt
function recordLoginAttempt($db, $email) {
    $stmt = $db->prepare('INSERT INTO login_attempts (email, attempt_time) VALUES (?, NOW())');
    $stmt->execute([$email]);
}

// Function to reset login attempts
function resetLoginAttempts($db, $email) {
    $stmt = $db->prepare('DELETE FROM login_attempts WHERE email = ?');
    $stmt->execute([$email]);
}

$csrfToken = Middleware::generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduClear - Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem;
        }
        .login-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .form-control {
            border-radius: 20px;
            padding: 12px 20px;
        }
        .btn-login {
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-graduation-cap login-icon"></i>
                    <h4 class="mb-0">EduClear Login</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required autocomplete="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required autocomplete="current-password">
                                <div class="input-group-append">
                                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember">
                                <label class="custom-control-label" for="rememberMe">Remember me</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/forgot-password.php" class="text-muted">
                            <small>Forgot your password?</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
