<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/Controller.php';
require_once __DIR__ . '/../app/models/Model.php';

// Simple router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Define routes
$routes = [
    '' => ['HomeController', 'index'],
    'login' => ['AuthController', 'login'],
    'register' => ['AuthController', 'register'],
    'logout' => ['AuthController', 'logout'],
    'dashboard' => ['DashboardController', 'index'],
    'payment' => ['PaymentController', 'index'],
    'clearance' => ['ClearanceController', 'index'],
];

// Route to controller
if (array_key_exists($uri, $routes)) {
    list($controller, $action) = $routes[$uri];
    $controllerFile = __DIR__ . "/../app/controllers/{$controller}.php";
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controllerInstance = new $controller();
        $controllerInstance->$action();
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
} else {
    http_response_code(404);
    require __DIR__ . '/../app/views/errors/404.php';
}
