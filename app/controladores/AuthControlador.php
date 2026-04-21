<?php

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/EventoModelo.php';
use App\modelos\UsuarioModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;

require_once 'app/Helpers/Validador.php';

class AuthControlador {

    //--------------------------------------------------------------------
    // Muestra la pantalla de inicio de sesión
    //--------------------------------------------------------------------

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

    //--------------------------------------------------------------------
    // Procesa la solicitud POST de inicio de sesión
    //--------------------------------------------------------------------

    public function authenticate() {
        header('Content-Type: application/json');
        try {
            //validacion de metodo
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            $usuario = trim($_POST['usuario']);
            $password = $_POST['password'];

            $campos_requeridos = ['usuario', 'password'];
            
            //Validacion De Campos Vacios
            foreach ($campos_requeridos as $campo) {
                if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                    echo json_encode([
                        'success' => false, 
                        'message' => "El campo {$campo} es obligatorio."
                    ]);
                    return; 
                }
            }
            // Validaciones encapsuladas a través del Validador común
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
            //validacion de usuario
            $usuarioModelo = new UsuarioModelo();
            $usuario_datos = $usuarioModelo->obtenerUsuarioPorNombre($usuario);

            if ($usuario_datos) {
                // Verificar contraseña cifrada
                if (password_verify($password, $usuario_datos['password'])) {
                    
                    // Prevención Session Fixation: generar nuevo ID tras autenticar
                    session_regenerate_id(true);

                    // Guardar datos en sesión
                    $_SESSION['user_id']     = $usuario_datos['id'];
                    $_SESSION['user_name']   = $usuario_datos['nombre_completo'];
                    $_SESSION['user_rol']    = $usuario_datos['nombre_rol'];
                    $_SESSION['user_rol_id'] = $usuario_datos['rol_id'];

                    // Cargar mapa de permisos en sesión (una sola query al login)
                    // Ejemplo: ['fichas' => ['ver','crear'], 'usuarios' => ['ver','gestionar']]
                    $_SESSION['permisos'] = $usuarioModelo->obtenerPermisosDeRol((int)$usuario_datos['rol_id']);

                    // Registrar evento de sesión iniciada
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

    //--------------------------------------------------------------------
    // Cierra la sesión
    //--------------------------------------------------------------------

    public function logout() {
        try {
            // Registrar evento antes de destruir la sesión
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
