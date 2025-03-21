<?php
abstract class Controller {
    protected function render($view, $data = []) {
        extract($data);
        
        $viewPath = __DIR__ . "/../views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new Exception("View {$view} not found");
        }
        
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        require __DIR__ . "/../views/layouts/main.php";
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function getPost($key = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? null;
    }

    protected function getQuery($key = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? null;
    }
}
