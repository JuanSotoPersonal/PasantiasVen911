<?php
ob_start();
session_start();

// Front Controller - index.php
// Este archivo recibe todas las peticiones y llama al controlador correspondiente.

// Autocarga manual muy simple para controladores
spl_autoload_register(function ($class) {
    if (file_exists('app/controladores/' . $class . '.php')) {
        require_once 'app/controladores/' . $class . '.php';
    } elseif (file_exists('app/modelos/' . $class . '.php')) {
        require_once 'app/modelos/' . $class . '.php';
    }
});

// Enrutador muy básico
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'auth';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerBaseName = ucfirst($url[0]);

// Mapeo especial para setup -> registro
if ($controllerBaseName === 'Setup') {
    $controllerBaseName = 'Registro';
}

$controllerName = $controllerBaseName . 'Controlador';

// ==========================================
// HELPER GLOBAL DE PERMISOS
// Uso: tienePerm('fichas', 'crear')  → true/false
// ==========================================
function tienePerm(string $modulo, string $permiso = 'ver'): bool {
    // SuperAdmin (Rol 1) tiene acceso total automático
    if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == 1) {
        return true;
    }
    
    return isset($_SESSION['permisos'][$modulo])
        && in_array($permiso, $_SESSION['permisos'][$modulo], true);
}

// ==========================================
// MIDDLEWARE GLOBAL DE AUTENTICACIÓN
// ==========================================
$isLoggedIn = isset($_SESSION['user_id']);
$methodRequested = isset($url[1]) ? $url[1] : '';

// 1. Si NO está logeado y la ruta no es 'auth' o 'setup', redirigir al login
if (!$isLoggedIn && $controllerBaseName !== 'Auth' && $controllerBaseName !== 'Registro') {
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
// RBAC MIDDLEWARE: protección de módulos por permiso
// Mapeo: [clave_url] => [modulo_rbac, permiso_minimo]
// ==========================================
if ($isLoggedIn) {
    // ==========================================
    // LAZY LOADING: Carga de permisos si faltan (post-update o sesión persistente)
    // ==========================================
    if (!isset($_SESSION['permisos'])) {
        require_once 'app/modelos/UsuarioModelo.php';
        $modeloPerm = new \App\modelos\UsuarioModelo();
        $_SESSION['permisos'] = $modeloPerm->obtenerPermisosDeRol((int)$_SESSION['user_rol_id']);
    }

    $rutasProtegidas = [
        'usuario'         => ['usuarios',      'ver'],
        'log'             => ['historial',      'ver'],
        'notificacion'    => ['fichas',         'ver'], // requiere acceso a fichas mínimo
    ];

    $claveRuta = strtolower($controllerBaseName);
    if (isset($rutasProtegidas[$claveRuta])) {
        [$moduloReq, $permisoReq] = $rutasProtegidas[$claveRuta];
        if (!tienePerm($moduloReq, $permisoReq)) {
            header('Location: index.php?url=home');
            exit;
        }
    }
}
// ==========================================


if (file_exists('app/controladores/' . $controllerName . '.php')) {
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
