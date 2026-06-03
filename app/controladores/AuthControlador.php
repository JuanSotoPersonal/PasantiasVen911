<?php
/**
 * CONTROLADOR: AuthControlador
 * Propósito: Gestionar el acceso al sistema, validación de credenciales y sesiones.
 * Implementa medidas de seguridad como regeneración de IDs y auditoría de accesos.
 */

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/RegistroModelo.php';
require_once 'app/modelos/EventoModelo.php';
require_once 'app/Helpers/Validador.php';

use App\modelos\UsuarioModelo;
use App\modelos\RegistroModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;

class AuthControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private UsuarioModelo  $modelo;
    private RegistroModelo $modeloRegistro;
    private EventoModelo   $modeloEvento;

    /**
     * Instancia los modelos de autenticación y auditoría.
     * No valida sesión aquí: este controlador es el punto de entrada.
     */
    public function __construct() {
        $this->modelo         = new UsuarioModelo();
        $this->modeloRegistro = new RegistroModelo();
        $this->modeloEvento   = new EventoModelo();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. RENDERIZADO (INICIO DE SESIÓN)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Despliega la pantalla de login.
     * Detecta si el sistema requiere configuración inicial (Setup).
     */
    public function index() {
        try {
            $conteoUsuarios   = $this->modelo->contarUsuarios();
            $puedeRegistrarse = ($conteoUsuarios === 0);
            
            require_once 'app/vista/login.php';
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en index: " . $e->getMessage());
            die("Ocurrió un error inesperado en el servidor.");
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. LÓGICA DE AUTENTICACIÓN (LOGIN POST)
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

            // 3.1 Verificación de campos obligatorios
            $campos_requeridos = ['usuario', 'password'];
            foreach ($campos_requeridos as $campo) {
                if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                    echo json_encode(['success' => false, 'message' => "El campo {$campo} es obligatorio."]);
                    return; 
                }
            }

            // 3.2 Validaciones de formato vía Helper Validador
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

            // 3.3 Recuperación y validación de hash de contraseña
            $usuario_datos = $this->modelo->obtenerUsuarioPorNombre($usuario);

            if ($usuario_datos) {
                if (password_verify($password, $usuario_datos['password'])) {
                    
                    // PREVENCIÓN DE SEGURIDAD: Regeneración de ID para evitar Session Fixation
                    session_regenerate_id(true);

                    // 3.4 Establecimiento de variables de sesión
                    $_SESSION['user_id']     = $usuario_datos['id'];
                    $_SESSION['user_name']   = $usuario_datos['nombre_completo'];
                    $_SESSION['user_rol']    = $usuario_datos['nombre_rol'];
                    $_SESSION['user_rol_id'] = $usuario_datos['rol_id'];

                    // 3.5 Carga persistente de permisos para validación RBAC
                    $_SESSION['permisos'] = $this->modelo->obtenerPermisosDeRol((int)$usuario_datos['rol_id']);

                    // Auditoría de ingreso
                    $this->modeloEvento->registrarEvento((int)$usuario_datos['id'], 'LOGIN', 'usuarios', (int)$usuario_datos['id'], null, null, "Usuario '{$usuario}' inició sesión.");

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
    // 4. CIERRE DE SESIÓN (LOGOUT)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Termina la sesión actual de forma segura y registra el evento.
     */
    public function logout() {
        try {
            // Auditoría previa a la destrucción de datos
            if (isset($_SESSION['user_id'])) {
                $this->modeloEvento->registrarEvento((int)$_SESSION['user_id'], 'LOGOUT', 'usuarios', (int)$_SESSION['user_id'], null, null, "Usuario '{$_SESSION['user_name']}' cerró sesión.");
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

    // ///////////////////////////////////////////////////////////////////
    // 5. RECUPERACIÓN DE CONTRASEÑA POR PREGUNTAS DE SEGURIDAD
    // ///////////////////////////////////////////////////////////////////

    /**
     * Paso 1: Valida que el usuario exista y sea SuperAdministrador.
     * Retorna los textos de sus preguntas de seguridad.
     */
    public function recuperarPaso1() {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';

            if ($usuario === '') {
                echo json_encode(['success' => false, 'message' => 'El nombre de usuario es obligatorio.']);
                return;
            }

            // Buscar usuario
            $usuario_datos = $this->modelo->obtenerUsuarioPorNombre($usuario);
            if (!$usuario_datos) {
                echo json_encode(['success' => false, 'message' => 'El usuario no existe o está inactivo.']);
                return;
            }

            // Restricción: solo SuperAdministrador (rol_id = 1)
            if ((int)$usuario_datos['rol_id'] !== 1) {
                echo json_encode(['success' => false, 'message' => 'Esta opción solo está permitida para el Administrador del Sistema.']);
                return;
            }

            // Obtener preguntas
            $preguntas = $this->modelo->obtenerPreguntasUsuario((int)$usuario_datos['id']);
            if (!$preguntas) {
                echo json_encode(['success' => false, 'message' => 'El Administrador no tiene preguntas de seguridad configuradas.']);
                return;
            }

            echo json_encode([
                'success' => true,
                'user_id' => (int)$usuario_datos['id'],
                'preguntas' => $preguntas
            ]);
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en recuperarPaso1: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    /**
     * Paso 2: Valida las respuestas de seguridad.
     * Si son correctas, almacena la validación temporalmente en sesión.
     */
    public function recuperarPaso2() {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            // Control de fuerza bruta (Rate Limiting para Preguntas)
            if (!isset($_SESSION['intentos_preguntas'])) {
                $_SESSION['intentos_preguntas'] = 0;
            }
            if ($_SESSION['intentos_preguntas'] >= 3) {
                echo json_encode(['success' => false, 'message' => 'Ha superado el límite de 3 intentos fallivos para las preguntas de seguridad. Proceso bloqueado temporalmente.']);
                return;
            }

            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $r1      = isset($_POST['respuesta_1']) ? trim($_POST['respuesta_1']) : '';
            $r2      = isset($_POST['respuesta_2']) ? trim($_POST['respuesta_2']) : '';

            if ($user_id <= 0 || $r1 === '' || $r2 === '') {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
                return;
            }

            // Verificar que el usuario sigue existiendo y sea SuperAdmin
            $usuario = $this->modelo->obtenerPorId($user_id);
            if (!$usuario || (int)$usuario['rol_id'] !== 1) {
                echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
                return;
            }

            // Verificar respuestas de seguridad
            if ($this->modelo->verificarRespuestasSeguridad($user_id, $r1, $r2)) {
                // Almacenar ID validado en sesión
                $_SESSION['recuperacion_usuario_id'] = $user_id;
                $_SESSION['intentos_preguntas']      = 0; // Resetear intentos
                echo json_encode(['success' => true, 'message' => 'Preguntas de seguridad validadas con éxito.']);
            } else {
                $_SESSION['intentos_preguntas']++;
                $intentos_restantes = 3 - $_SESSION['intentos_preguntas'];
                $msg = "Las respuestas de seguridad son incorrectas. ";
                if ($intentos_restantes > 0) {
                    $msg .= "Le quedan {$intentos_restantes} intento(s).";
                } else {
                    $msg .= "Proceso bloqueado por seguridad.";
                }
                echo json_encode(['success' => false, 'message' => $msg]);
            }
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en recuperarPaso2: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    /**
     * Paso 3: Cambia la contraseña tras la validación previa en sesión.
     */
    public function recuperarPaso3() {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            $user_id   = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $password  = isset($_POST['nueva_password']) ? $_POST['nueva_password'] : '';
            $confirmar = isset($_POST['confirmar_password']) ? $_POST['confirmar_password'] : '';

            // Validación de flujo seguro en sesión
            if (!isset($_SESSION['recuperacion_usuario_id']) || (int)$_SESSION['recuperacion_usuario_id'] !== $user_id) {
                echo json_encode(['success' => false, 'message' => 'Sesión de recuperación inválida o expirada. Por favor, inicie de nuevo el proceso.']);
                return;
            }

            if ($password === '' || $confirmar === '') {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
                return;
            }

            if ($password !== $confirmar) {
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
                return;
            }

            // Validar fortaleza de la contraseña vía helper Validador
            $valPass = Validador::validarContrasena($password);
            if (!$valPass['valido']) {
                echo json_encode(['success' => false, 'message' => $valPass['mensaje']]);
                return;
            }

            // Cambiar contraseña
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($this->modelo->actualizarContrasena($user_id, $hash)) {
                // Registrar auditoría bajo el ID del propio administrador
                $this->modeloEvento->registrarEvento($user_id, 'UPDATE', 'usuarios', $user_id, null, null, "Contraseña recuperada exitosamente mediante preguntas de seguridad.");

                // Limpiar variable de sesión segura
                unset($_SESSION['recuperacion_usuario_id']);

                echo json_encode(['success' => true, 'message' => 'Contraseña restablecida exitosamente. Ahora puede ingresar al sistema.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la contraseña en la base de datos.']);
            }
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en recuperarPaso3: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    /**
     * Obtiene el catálogo completo de preguntas de seguridad registradas en el sistema.
     */
    public function obtenerTodasPreguntas() {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            // Validar que se solicite con un usuario_id del SuperAdmin para evitar descargas ociosas
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $usuario = $this->modelo->obtenerPorId($user_id);
            if (!$usuario || (int)$usuario['rol_id'] !== 1) {
                echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
                return;
            }

            $preguntas = $this->modeloRegistro->obtenerPreguntasSeguridad();
            echo json_encode([
                'success' => true,
                'preguntas' => $preguntas
            ]);
        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en obtenerTodasPreguntas: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    /**
     * Valida el Código de Fábrica (Llave de Activación) e inicializa el restablecimiento
     * de las preguntas y respuestas secretas del SuperAdministrador.
     */
    public function restablecerPreguntasConLlave() {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            // Control de fuerza bruta (Rate Limiting para Clave de Activación)
            if (!isset($_SESSION['intentos_llave'])) {
                $_SESSION['intentos_llave'] = 0;
            }
            if ($_SESSION['intentos_llave'] >= 3) {
                echo json_encode(['success' => false, 'message' => 'Ha superado el límite de 3 intentos para el Código de Activación. Proceso bloqueado temporalmente.']);
                return;
            }

            $user_id      = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $factory_code = isset($_POST['factory_code']) ? trim($_POST['factory_code']) : '';
            $p1           = isset($_POST['pregunta_1']) ? (int)$_POST['pregunta_1'] : 0;
            $p2           = isset($_POST['pregunta_2']) ? (int)$_POST['pregunta_2'] : 0;
            $r1           = isset($_POST['respuesta_1']) ? trim($_POST['respuesta_1']) : '';
            $r2           = isset($_POST['respuesta_2']) ? trim($_POST['respuesta_2']) : '';

            if ($user_id <= 0 || $factory_code === '' || $p1 <= 0 || $p2 <= 0 || $r1 === '' || $r2 === '') {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
                return;
            }

            // 1. Validar que el usuario sea SuperAdmin (rol_id === 1)
            $usuario = $this->modelo->obtenerPorId($user_id);
            if (!$usuario || (int)$usuario['rol_id'] !== 1) {
                echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo el Administrador puede realizar esta acción.']);
                return;
            }

            // 2. Validar Llave de Activación
            if (!$this->modeloRegistro->validarLlaveActivacion($factory_code)) {
                $_SESSION['intentos_llave']++;
                $intentos_restantes = 3 - $_SESSION['intentos_llave'];
                $msg = 'Código de activación de sistema incorrecto. ';
                if ($intentos_restantes > 0) {
                    $msg .= "Le quedan {$intentos_restantes} intento(s).";
                } else {
                    $msg .= 'Proceso bloqueado por seguridad.';
                }
                
                // Auditar intento fallido de bypass (Alta prioridad)
                $this->modeloEvento->registrarEvento($user_id, 'UPDATE', 'usuarios', $user_id, null, null, "FALLÓ validación de Código de Activación al intentar recuperar preguntas.");
                
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }

            // Resetear intentos de llave tras acierto
            $_SESSION['intentos_llave'] = 0;

            // 3. Validaciones de integridad de preguntas
            if ($p1 === $p2) {
                echo json_encode(['success' => false, 'message' => 'Debes seleccionar dos preguntas de seguridad diferentes.']);
                return;
            }

            $valR1 = Validador::validarRespuestaSeguridad($r1);
            if (!$valR1['valido']) {
                echo json_encode(['success' => false, 'message' => 'Respuesta 1: ' . $valR1['mensaje']]);
                return;
            }

            $valR2 = Validador::validarRespuestaSeguridad($r2);
            if (!$valR2['valido']) {
                echo json_encode(['success' => false, 'message' => 'Respuesta 2: ' . $valR2['mensaje']]);
                return;
            }

            // 4. Actualizar preguntas y respuestas secretas (hasheadas)
            $datosSeguridad = [
                'pregunta_1_id' => $p1,
                'pregunta_2_id' => $p2,
                'respuesta_1'   => password_hash(strtolower($r1), PASSWORD_DEFAULT),
                'respuesta_2'   => password_hash(strtolower($r2), PASSWORD_DEFAULT)
            ];

            if ($this->modelo->actualizarCamposSeguridad($user_id, $datosSeguridad)) {
                // Establecer sesión de recuperación para habilitar el Paso 3
                $_SESSION['recuperacion_usuario_id'] = $user_id;
                $_SESSION['intentos_preguntas']      = 0; // Resetear intentos de preguntas también

                // Auditar restablecimiento exitoso
                $this->modeloEvento->registrarEvento($user_id, 'UPDATE', 'usuarios', $user_id, null, null, "Preguntas de seguridad restablecidas con Código de Activación.");

                echo json_encode(['success' => true, 'message' => 'Preguntas de seguridad restablecidas con éxito. Procediendo al cambio de contraseña.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar los nuevos datos de seguridad en el servidor.']);
            }

        } catch (\Exception $e) {
            error_log("[AuthControlador] Error en restablecerPreguntasConLlave: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }
}
