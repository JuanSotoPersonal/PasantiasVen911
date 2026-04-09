<?php
ob_start();
session_start();

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
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'auth';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerBaseName = ucfirst($url[0]);
$controllerName = $controllerBaseName . 'Controller';

// ==========================================
// MIDDLEWARE GLOBAL DE AUTENTICACIÓN
// ==========================================
$isLoggedIn = isset($_SESSION['user_id']);
$methodRequested = isset($url[1]) ? $url[1] : '';

// 1. Si NO está logeado y la ruta no es 'auth' o 'setup', redirigir al login
if (!$isLoggedIn && $controllerBaseName !== 'Auth' && $controllerBaseName !== 'Setup') {
    header('Location: index.php?url=auth');
    exit;
}

// 2. Si ESTÁ logeado, impedir que regrese a la pantalla de login ('auth' a secas),
// a menos que esté solicitando hacer 'logout'
if ($isLoggedIn && $controllerBaseName === 'Auth' && $methodRequested !== 'logout' && $methodRequested !== 'authenticate') {
    header('Location: index.php?url=home');
    exit;
}
// ==========================================

if (file_exists('app/Controllers/' . $controllerName . '.php')) {
    $controller = new $controllerName();
    
    // Método por defecto en MVC siempre es 'index'
    $method = isset($url[1]) && !empty($url[1]) ? $url[1] : 'index';

    if (method_exists($controller, $method)) {
        unset($url[0], $url[1]);
        // Re-indexar los parámetros restantes si hubieren
        $params = $url ? array_values($url) : [];
        // Limpiar buffer antes de ejecutar métodos que responden JSON
        ob_end_clean();
        call_user_func_array([$controller, $method], $params);
    } else {
        // Si el método no existe dentro de ese controlador específico
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>El método <strong>$method</strong> no existe en el controlador <strong>$controllerName</strong>.</p>";
    }
} else {
    // 404
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>El controlador <strong>$controllerName</strong> no existe.</p>";
}
