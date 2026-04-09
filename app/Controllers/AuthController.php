<?php

require_once 'app/Models/Usuario.php';
require_once 'app/Models/LogModel.php';
use App\Models\Usuario;
use App\Models\LogModel;

class AuthController {

    //--------------------------------------------------------------------
    // Muestra la pantalla de inicio de sesión
    //--------------------------------------------------------------------

    public function index() {
        $usuarioModel = new Usuario();
        $userCount = $usuarioModel->countUsers();
        $canRegister = ($userCount === 0);
        
        require_once 'app/Views/login.php';
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
        //validacion de longitud de contraseña
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }
        //validacion de usuario
        $usuarioModel = new Usuario();
        $user = $usuarioModel->getUsuarioByUsername($usuario);

        if ($user) {
            // Verificar contraseña cifrada
            if (password_verify($password, $user['password'])) {
                
                // Guardar datos en sesión
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['user_name']   = $user['nombre_completo'];
                $_SESSION['user_rol']    = $user['nombre_rol'];
                $_SESSION['user_rol_id'] = $user['rol_id'];

                // Registrar log de sesión iniciada
                $log = new LogModel();
                $log->registrar((int)$user['id'], 'LOGIN', 'usuarios', (int)$user['id'], null, null, "Usuario '{$usuario}' inició sesión.");

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
        // Registrar log antes de destruir la sesión
        if (isset($_SESSION['user_id'])) {
            $log = new LogModel();
            $log->registrar((int)$_SESSION['user_id'], 'LOGOUT', 'usuarios', (int)$_SESSION['user_id'], null, null, "Usuario '{$_SESSION['user_name']}' cerró sesión.");
        }
        session_destroy();
        header('Location: index.php?url=auth');
        exit;
    }
}
