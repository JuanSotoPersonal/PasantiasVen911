<?php

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/EventoModelo.php';
use App\modelos\UsuarioModelo;
use App\modelos\EventoModelo;

class AuthControlador {

    //--------------------------------------------------------------------
    // Muestra la pantalla de inicio de sesión
    //--------------------------------------------------------------------

    public function index() {
        $usuarioModelo = new UsuarioModelo();
        $conteoUsuarios = $usuarioModelo->contarUsuarios();
        $puedeRegistrarse = ($conteoUsuarios === 0);
        
        require_once 'app/vista/login.php';
    }

    //--------------------------------------------------------------------
    // Procesa la solicitud POST de inicio de sesión
    //--------------------------------------------------------------------

    public function authenticate() {
        header('Content-Type: application/json');
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
        //validacion de formato de usuario
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            echo json_encode(['success' => false, 'message' => 'El usuario solo puede contener letras y números, sin signos especiales.']);
            return;
        }
        //validacion de longitud de usuario
        if (strlen($usuario) < 7) {
            echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 7 caracteres.']);
            return;
        }
        if (strlen($usuario) > 32) {
            echo json_encode(['success' => false, 'message' => 'El usuario no puede exceder los 32 caracteres.']);
            return;
        }

        //validacion de longitud de contraseña
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.']);
            return;
        }
        if (strlen($password) > 128) {
            echo json_encode(['success' => false, 'message' => 'La contraseña no puede exceder los 128 caracteres.']);
            return;
        }
        //validacion de usuario
        $usuarioModelo = new UsuarioModelo();
        $usuario_datos = $usuarioModelo->obtenerUsuarioPorNombre($usuario);

        if ($usuario_datos) {
            // Verificar contraseña cifrada
            if (password_verify($password, $usuario_datos['password'])) {
                
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
    }

    //--------------------------------------------------------------------
    // Cierra la sesión
    //--------------------------------------------------------------------

    public function logout() {
        // Registrar evento antes de destruir la sesión
        if (isset($_SESSION['user_id'])) {
            $evento = new EventoModelo();
            $evento->registrarEvento((int)$_SESSION['user_id'], 'LOGOUT', 'usuarios', (int)$_SESSION['user_id'], null, null, "Usuario '{$_SESSION['user_name']}' cerró sesión.");
        }
        session_destroy();
        header('Location: index.php?url=auth');
        exit;
    }
}
