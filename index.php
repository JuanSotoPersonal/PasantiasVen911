<?php

// Front Controller - index.php
// Este archivo recibe todas las peticiones y llama al controlador correspondiente.

// Autocarga manual muy simple para controladores
spl_autoload_register(function ($class) {
    if (file_exists('app/Controllers/' . $class . '.php')) {
        require_once 'app/Controllers/' . $class . '.php';
    } elseif (file_exists('app/Models/' . $class . '.php')) {
        require_once 'app/Models/' . $class . '.php';
    }
});

// Enrutador muy básico
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerName = ucfirst($url[0]) . 'Controller';

if (file_exists('app/Controllers/' . $controllerName . '.php')) {
    $controller = new $controllerName();
    
    // Si hay un método especificado en la URL
    if (isset($url[1]) && method_exists($controller, $url[1])) {
        $method = $url[1];
        unset($url[0], $url[1]);
        call_user_func_array([$controller, $method], $url);
    } else {
        // Por defecto llama al método index
        $controller->index();
    }
} else {
    // 404
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>El controlador <strong>$controllerName</strong> no existe.</p>";
}
