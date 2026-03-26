<?php

require_once 'app/Models/Usuario.php';
use App\Models\Usuario;

class AuthController {
    
    /**
     * Muestra la pantalla de inicio de sesión
     */
    public function auth() {
        require_once 'app/Views/login.php';
    }

    /**
     * Procesa la solicitud POST de inicio de sesión
     */
    public function authenticate() {
        // Asegurarnos que la respuesta sea JSON
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Por favor, ingrese todos los datos.']);
            return;
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->getUsuarioByUsername($email);

        if ($user) {
            // Verificar contraseña cifrada
            if (password_verify($password, $user['password'])) {
                
                // Guardar datos en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre_completo'];
                $_SESSION['user_rol'] = $user['nombre_rol'];
                $_SESSION['user_rol_id'] = $user['rol_id'];

                echo json_encode(['success' => true, 'message' => 'Autenticación exitosa.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Credenciales inválidas.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Credenciales inválidas.']);
        }
    }

    /**
     * Cierra la sesión
     */
    public function logout() {
        session_destroy();
        header('Location: index.php?url=auth');
        exit;
    }
}
