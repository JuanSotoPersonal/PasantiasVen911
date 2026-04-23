<?php
/**
 * CONTROLADOR: AuthControlador
 * Propósito: Gestionar el acceso al sistema, validación de credenciales y sesiones.
 * Implementa medidas de seguridad como regeneración de IDs y auditoría de accesos.
 */

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/EventoModelo.php';
use App\modelos\UsuarioModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;

require_once 'app/Helpers/Validador.php';

class AuthControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. RENDERIZADO (INICIO DE SESIÓN)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Despliega la pantalla de login.
     * Detecta si el sistema requiere configuración inicial (Setup).
     */
    public function index() {
        try {
            $usuarioModelo = new UsuarioModelo();
            $conteoUsuarios = $usuarioModelo->contarUsuarios();
            $puedeRegistrarse = ($conteoUsuarios === 0);
            
            require_once 'app/vista/login.php';
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en index: " . $e->getMessage());
            die("Ocurrió un error inesperado en el servidor.");
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. LÓGICA DE AUTENTICACIÓN (LOGIN POST)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa las credenciales de usuario y establece la sesión.
     * Incluye validación de formatos, cifrado y carga de permisos RBAC.
     */
    public function authenticate() {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            $usuario  = trim($_POST['usuario']);
            $password = $_POST['password'];

            // 2.1 Verificación de campos obligatorios
            $campos_requeridos = ['usuario', 'password'];
            foreach ($campos_requeridos as $campo) {
                if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                    echo json_encode(['success' => false, 'message' => "El campo {$campo} es obligatorio."]);
                    return; 
                }
            }

            // 2.2 Validaciones de formato vía Helper Validador
            $valUsuario = Validador::validarUsuario($usuario);
            if (!$valUsuario['valido']) {
                echo json_encode(['success' => false, 'message' => $valUsuario['mensaje']]);
                return;
            }

            $valPass = Validador::validarContrasena($password);
            if (!$valPass['valido']) {
                echo json_encode(['success' => false, 'message' => $valPass['mensaje']]);
                return;
            }

            // 2.3 Recuperación y validación de hash de contraseña
            $usuarioModelo = new UsuarioModelo();
            $usuario_datos = $usuarioModelo->obtenerUsuarioPorNombre($usuario);

            if ($usuario_datos) {
                if (password_verify($password, $usuario_datos['password'])) {
                    
                    // PREVENCIÓN DE SEGURIDAD: Regeneración de ID para evitar Session Fixation
                    session_regenerate_id(true);

                    // 2.4 Establecimiento de variables de sesión
                    $_SESSION['user_id']     = $usuario_datos['id'];
                    $_SESSION['user_name']   = $usuario_datos['nombre_completo'];
                    $_SESSION['user_rol']    = $usuario_datos['nombre_rol'];
                    $_SESSION['user_rol_id'] = $usuario_datos['rol_id'];

                    // 2.5 Carga persistente de permisos para validación RBAC
                    $_SESSION['permisos'] = $usuarioModelo->obtenerPermisosDeRol((int)$usuario_datos['rol_id']);

                    // Auditoría de ingreso
                    $evento = new EventoModelo();
                    $evento->registrarEvento((int)$usuario_datos['id'], 'LOGIN', 'usuarios', (int)$usuario_datos['id'], null, null, "Usuario '{$usuario}' inició sesión.");

                    echo json_encode(['success' => true, 'message' => 'Autenticación exitosa.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Credenciales inválidas.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Credenciales inválidas.']);
            }
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en authenticate: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. CIERRE DE SESIÓN (LOGOUT)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Termina la sesión actual de forma segura y registra el evento.
     */
    public function logout() {
        try {
            // Auditoría previa a la destrucción de datos
            if (isset($_SESSION['user_id'])) {
                $evento = new EventoModelo();
                $evento->registrarEvento((int)$_SESSION['user_id'], 'LOGOUT', 'usuarios', (int)$_SESSION['user_id'], null, null, "Usuario '{$_SESSION['user_name']}' cerró sesión.");
            }
            session_destroy();
            header('Location: index.php?url=auth');
            exit;
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en logout: " . $e->getMessage());
            session_destroy();
            header('Location: index.php?url=auth');
            exit;
        }
    }
}
