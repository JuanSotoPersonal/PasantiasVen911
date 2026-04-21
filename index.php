<?php
ob_start();

// ==========================================
// SEGURIDAD GLOBAL: Hardening de la Sesión
// Previene secuestro de sesión (XSS) y CSRF.
// ==========================================
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',       // Ajustar a dominio real en prod
    'secure'   => true,     // Envío solo en HTTPS
    'httponly' => true,     // Bloqueo lectura desde JS
    'samesite' => 'Strict'  // Bloqueo ataques CSRF
]);

session_start();

// Cabeceras de seguridad HTTP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

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
$segmentos = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'auth';
$segmentos = filter_var($segmentos, FILTER_SANITIZE_URL);
$segmentos = explode('/', $segmentos);

$nombreBaseControlador = ucfirst($segmentos[0]);

// Mapeo especial para setup -> registro
if ($nombreBaseControlador === 'Setup') {
    $nombreBaseControlador = 'Registro';
}

$nombreControlador = $nombreBaseControlador . 'Controlador';

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
$estaAutenticado  = isset($_SESSION['user_id']);
$metodoSolicitado = isset($segmentos[1]) ? $segmentos[1] : '';

// 1. Si NO está logueado y la ruta no es 'auth' o 'setup', redirigir al login
if (!$estaAutenticado && $nombreBaseControlador !== 'Auth' && $nombreBaseControlador !== 'Registro') {
    header('Location: index.php?url=auth');
    exit;
}

// 2. Si ESTÁ logueado, impedir que regrese a la pantalla de login ('auth' a secas),
// a menos que esté solicitando hacer 'logout'
if ($estaAutenticado && $nombreBaseControlador === 'Auth' && $metodoSolicitado !== 'logout' && $metodoSolicitado !== 'authenticate') {
    header('Location: index.php?url=home');
    exit;
}

// ==========================================
// RBAC MIDDLEWARE: protección de módulos por permiso
// Mapeo: [clave_url] => [modulo_rbac, permiso_minimo]
// ==========================================
if ($estaAutenticado) {
    // ==========================================
    // LAZY LOADING: Carga de permisos si faltan (post-update o sesión persistente)
    // ==========================================
    if (!isset($_SESSION['permisos'])) {
        require_once 'app/modelos/UsuarioModelo.php';
        $modeloPermisos = new \App\modelos\UsuarioModelo();
        $_SESSION['permisos'] = $modeloPermisos->obtenerPermisosDeRol((int)$_SESSION['user_rol_id']);
    }

    $rutasProtegidas = [
        'usuario'      => ['usuarios',  'ver'],
        'evento'       => ['historial', 'ver'],
        'notificacion' => ['fichas',    'ver'],
        'ficha'        => ['fichas',    'ver'],
    ];

    $claveRuta = strtolower($nombreBaseControlador);
    if (isset($rutasProtegidas[$claveRuta])) {
        [$moduloRequerido, $permisoRequerido] = $rutasProtegidas[$claveRuta];
        if (!tienePerm($moduloRequerido, $permisoRequerido)) {
            header('Location: index.php?url=home');
            exit;
        }
    }
}
// ==========================================


if (file_exists('app/controladores/' . $nombreControlador . '.php')) {
    $controlador = new $nombreControlador();

    // Método por defecto en MVC siempre es 'index'
    $metodo = isset($segmentos[1]) && !empty($segmentos[1]) ? $segmentos[1] : 'index';

    if (method_exists($controlador, $metodo)) {
        unset($segmentos[0], $segmentos[1]);
        // Re-indexar los parámetros restantes si hubieren
        $parametros = $segmentos ? array_values($segmentos) : [];
        // Limpiar buffer antes de ejecutar métodos que responden JSON
        ob_end_clean();
        call_user_func_array([$controlador, $metodo], $parametros);
    } else {
        // Si el método no existe dentro de ese controlador específico
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>El método <strong>$metodo</strong> no existe en el controlador <strong>$nombreControlador</strong>.</p>";
    }
} else {
    // 404
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>El controlador <strong>$nombreControlador</strong> no existe.</p>";
}
