<?php

require_once 'app/Models/UsuarioModel.php';
use App\Models\UsuarioModel;

class UsuarioController {

    private UsuarioModel $model;

    public function __construct() {
        // Solo el Super Admin (rol_id = 1) puede acceder a este módulo
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol_id'] != 1) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->model = new UsuarioModel();
    }

    /**
     * GET → Muestra la vista principal del módulo de usuarios.
     */
    public function index(): void {
        $roles = $this->model->getRoles();
        require_once 'app/Views/usuarios/index.php';
    }

    /**
     * GET → Retorna todos los usuarios en formato JSON (para DataTable).
     */
    public function getData(): void {
        header('Content-Type: application/json');
        $usuarios = $this->model->getAll();
        echo json_encode(['data' => $usuarios]);
    }

    /**
     * POST → Crea un nuevo usuario.
     */
    public function store(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $campos = ['usuario', 'password', 'nombre_completo', 'rol_id'];
        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                echo json_encode(['success' => false, 'message' => "El campo {$campo} es obligatorio."]);
                return;
            }
        }

        $usuario        = trim($_POST['usuario']);
        $password       = $_POST['password'];
        $nombreCompleto = trim($_POST['nombre_completo']);
        $cedula         = trim($_POST['cedula'] ?? '');
        $rolId          = (int)$_POST['rol_id'];
        $codigoOperador = trim($_POST['codigo_operador'] ?? '') ?: null;
        $estado         = 'activo';

        if (strlen($usuario) < 7) {
            echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 7 caracteres.']);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            echo json_encode(['success' => false, 'message' => 'El usuario solo puede contener letras y números.']);
            return;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }

        if ($this->model->usuarioExists($usuario)) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está registrado.']);
            return;
        }

        if ($codigoOperador && $this->model->codigoExists($codigoOperador)) {
            echo json_encode(['success' => false, 'message' => 'El código de operador ya está en uso.']);
            return;
        }

        $data = [
            'usuario'         => $usuario,
            'password'        => password_hash($password, PASSWORD_DEFAULT),
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'rol_id'          => $rolId,
            'codigo_operador' => $codigoOperador,
            'estado'          => $estado,
        ];

        if ($this->model->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario.']);
        }
    }

    /**
     * POST → Actualiza la información básica del usuario.
     */
    public function update(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $id             = (int)($_POST['id'] ?? 0);
        $usuario        = trim($_POST['usuario'] ?? '');
        $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
        $cedula         = trim($_POST['cedula'] ?? '');
        $rolId          = (int)($_POST['rol_id'] ?? 0);
        $codigoOperador = trim($_POST['codigo_operador'] ?? '') ?: null;

        if (!$id || empty($usuario) || empty($nombreCompleto) || !$rolId) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos.']);
            return;
        }

        if ($this->model->usuarioExists($usuario, $id)) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está registrado por otro usuario.']);
            return;
        }

        if ($codigoOperador && $this->model->codigoExists($codigoOperador, $id)) {
            echo json_encode(['success' => false, 'message' => 'El código de operador ya está en uso.']);
            return;
        }

        $data = [
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'usuario'         => $usuario,
            'rol_id'          => $rolId,
            'codigo_operador' => $codigoOperador,
        ];

        if ($this->model->updateInfo($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario.']);
        }
    }

    /**
     * POST → Cambia la contraseña de un usuario.
     */
    public function updatePassword(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $newPassword = $_POST['password'] ?? '';
        $confirmPass = $_POST['password_confirm'] ?? '';

        if (!$id || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            return;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }

        if ($newPassword !== $confirmPass) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            return;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($this->model->updatePassword($id, $hashed)) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.']);
        }
    }

    /**
     * POST → Alterna el estado activo/inactivo de un usuario.
     */
    public function toggleEstado(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        // Protección: no permitir deshabilitar al propio Super Admin que está logueado
        if ($id === (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio estado.']);
            return;
        }

        $resultado = $this->model->toggleEstado($id);

        if ($resultado !== false) {
            echo json_encode([
                'success'       => true,
                'message'       => 'Estado actualizado correctamente.',
                'nuevo_estado'  => $resultado['nuevo_estado'],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        }
    }
}
