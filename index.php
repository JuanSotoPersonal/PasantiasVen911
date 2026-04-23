<?php
/**
 * index.php - Front Controller Centralizado
 * 
 * Este archivo actúa como el núcleo del sistema, gestionando la seguridad de sesión,
 * el ruteo de peticiones (MVC), la autenticación global y el control de acceso (RBAC).
 */

ob_start();

// ----------------------------------------------------------------------
// 1. SEGURIDAD DE SESIÓN Y CABECERAS HTTP
// ----------------------------------------------------------------------

// Configuración de cookies para mitigar ataques XSS y CSRF
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',       // Ajustar a dominio real en producción
    'secure'   => true,     // Envío solo mediante HTTPS
    'httponly' => true,     // Impide acceso a la cookie desde JavaScript
    'samesite' => 'Strict'  // Bloquea solicitudes cruzadas de otros dominios
]);

session_start();

// Cabeceras de seguridad para endurecer el navegador (Hardening)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');


// ----------------------------------------------------------------------
// 2. INFRAESTRUCTURA Y CARGA AUTOMÁTICA (AUTOLOAD)
// ----------------------------------------------------------------------

/**
 * Autocargador simple de clases según la estructura de directorios.
 */
spl_autoload_register(function ($nombreClase) {
    $archivos = [
        'app/controladores/' . $nombreClase . '.php',
        'app/modelos/' . $nombreClase . '.php'
    ];
    
    foreach ($archivos as $archivo) {
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
    }
});


// ----------------------------------------------------------------------
// 3. PROCESAMIENTO DE URL Y ENRUTAMIENTO
// ----------------------------------------------------------------------

// Captura y limpieza de la URL delegada por .htaccess o ?url=
$urlRaw = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'auth';
$urlLimpia = filter_var($urlRaw, FILTER_SANITIZE_URL);
$segmentos = explode('/', $urlLimpia);

// Determinación del Controlador
$nombreBase = ucfirst($segmentos[0]);

// Mapeo especial para rutas amigables (ej: setup -> RegistroControlador)
if ($nombreBase === 'Setup') {
    $nombreBase = 'Registro';
}

$nombreControlador = $nombreBase . 'Controlador';


// ----------------------------------------------------------------------
// 4. AYUDANTES GLOBALES (HELPERS)
// ----------------------------------------------------------------------

/**
 * Verifica si el usuario actual tiene un permiso específico en un módulo.
 * 
 * @param string $modulo Nombre del módulo (ej: 'fichas', 'usuarios').
 * @param string $permiso Acción requerida (ej: 'ver', 'crear', 'editar').
 * @return bool
 */
function tienePerm(string $modulo, string $permiso = 'ver'): bool {
    // El Administrador (ID Rol: 1) tiene bypass de seguridad total
    if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == 1) {
        return true;
    }
    
    return isset($_SESSION['permisos'][$modulo])
        && in_array($permiso, $_SESSION['permisos'][$modulo], true);
}


// ----------------------------------------------------------------------
// 5. MIDDLEWARE DE AUTENTICACIÓN
// ----------------------------------------------------------------------

$autenticado = isset($_SESSION['user_id']);
$metodo      = $segmentos[1] ?? '';

// Redirección forzosa si intenta acceder al sistema sin estar logueado
if (!$autenticado && !in_array($nombreBase, ['Auth', 'Registro'])) {
    header('Location: index.php?url=auth');
    exit;
}

// Redirección si un usuario logueado intenta volver al login (excepto logout/auth)
if ($autenticado && $nombreBase === 'Auth' && !in_array($metodo, ['logout', 'authenticate'])) {
    header('Location: index.php?url=home');
    exit;
}


// ----------------------------------------------------------------------
// 6. MIDDLEWARE DE CONTROL DE ACCESO (RBAC)
// ----------------------------------------------------------------------

if ($autenticado) {
    // Lazy Loading: Recargar permisos si la sesión los perdió por tiempo o actualización
    if (!isset($_SESSION['permisos'])) {
        require_once 'app/modelos/UsuarioModelo.php';
        $modeloUser = new \App\modelos\UsuarioModelo();
        $_SESSION['permisos'] = $modeloUser->obtenerPermisosDeRol((int)$_SESSION['user_rol_id']);
    }

    // Listado de rutas protegidas y sus permisos mínimos requeridos
    $reglasRutas = [
        'usuario'      => ['usuarios',  'ver'],
        'evento'       => ['historial', 'ver'],
        'notificacion' => ['fichas',    'ver'],
        'ficha'        => ['fichas',    'ver'],
    ];

    $slugRuta = strtolower($nombreBase);
    if (isset($reglasRutas[$slugRuta])) {
        [$modReq, $permReq] = $reglasRutas[$slugRuta];
        if (!tienePerm($modReq, $permReq)) {
            header('Location: index.php?url=home');
            exit;
        }
    }
}


// ----------------------------------------------------------------------
// 7. EJECUCIÓN DEL CONTROLADOR (DESPACHO)
// ----------------------------------------------------------------------

$rutaFisicaControlador = 'app/controladores/' . $nombreControlador . '.php';

if (file_exists($rutaFisicaControlador)) {
    $objetoControlador = new $nombreControlador();
    
    // El método por defecto en el patrón MVC es 'index'
    $nombreMetodo = (!empty($segmentos[1])) ? $segmentos[1] : 'index';

    if (method_exists($objetoControlador, $nombreMetodo)) {
        // Preparación de parámetros adicionales de la URL
        unset($segmentos[0], $segmentos[1]);
        $parametros = array_values($segmentos);
        
        // Limpiamos el buffer antes de la ejecución para evitar outputs residuales en peticiones AJAX/JSON
        ob_end_clean();
        
        call_user_func_array([$objetoControlador, $nombreMetodo], $parametros);
    } else {
        // Error: El método especificado no existe
        http_response_code(404);
        echo "<h1>404 - Método No Encontrado</h1>";
        echo "<p>La acción '<strong>$nombreMetodo</strong>' no está definida en el controlador '$nombreControlador'.</p>";
    }
} else {
    // Error: El controlador solicitado no existe en el disco
    http_response_code(404);
    echo "<h1>404 - Recurso No Encontrado</h1>";
    echo "<p>El controlador solicitado ('<strong>$nombreControlador</strong>') no existe en el sistema.</p>";
}

