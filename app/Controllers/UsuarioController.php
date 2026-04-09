<?php

require_once 'app/Models/UsuarioModel.php';
require_once 'app/Models/LogModel.php';
use App\Models\UsuarioModel;
use App\Models\LogModel;

class UsuarioController {

    private UsuarioModel $model;

    //--------------------------------------------------------------------
    // Constructor
    //--------------------------------------------------------------------

    private LogModel $log;

    public function __construct() {
        // Validacion que solo el Super Admin Pueda Acceder
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol_id'] != 1) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->model = new UsuarioModel();
        $this->log   = new LogModel();
    }

    //--------------------------------------------------------------------
    // Muestra la vista principal del módulo de usuarios
    //--------------------------------------------------------------------

    public function index(): void {
        $roles = $this->model->getRoles();
        require_once 'app/Views/usuarios/index.php';
    }

    //--------------------------------------------------------------------
    // GET Retorna todos los usuarios en formato JSON (para DataTable)
    //--------------------------------------------------------------------

    public function getData(): void {
        header('Content-Type: application/json');
        $estado = $_GET['estado'] ?? 'activo';
        $usuarios = $this->model->getAll($estado);
        echo json_encode(['data' => $usuarios]);
    }

    //--------------------------------------------------------------------
    // GET Retorna usuarios de un rol específico en JSON (para DataTables por rol)
    //--------------------------------------------------------------------

    public function getDataByRol(): void {
        header('Content-Type: application/json');
        $rolId = (int)($_GET['rol_id'] ?? 0);
        $estado = $_GET['estado'] ?? 'activo';
        if (!$rolId || $rolId === 1) {
            echo json_encode(['data' => []]);
            return;
        }
        $usuarios = $this->model->getByRol($rolId, $estado);
        echo json_encode(['data' => $usuarios]);
    }

    //--------------------------------------------------------------------
    // POST Crea un nuevo usuario
    //--------------------------------------------------------------------

    public function store(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }
        //Validacion de campos vacios
        $campos = ['usuario', 'password', 'nombre_completo', 'cedula', 'rol_id'];
        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                $friendlyNames = [
                    'password' => 'contraseña',
                    'rol_id'   => 'rol'
                ];
                $nombreCampo = $friendlyNames[$campo] ?? str_replace('_', ' ', $campo);
                echo json_encode(['success' => false, 'message' => "El campo {$nombreCampo} es obligatorio."]);
                return;
            }
        }
        //Asignacion de variables
        $usuario        = trim($_POST['usuario']);
        $password       = $_POST['password'];
        $nombreCompleto = trim($_POST['nombre_completo']);
        $cedula         = trim($_POST['cedula'] ?? '');
        $rolId          = (int)$_POST['rol_id'];
        $codigoOperador = trim($_POST['codigo_operador'] ?? '') ?: null;
        $estado         = 'activo';
        //Validacion de longitud de cedula
        if (strlen($cedula) < 6 || strlen($cedula) > 8) {
            echo json_encode(['success' => false, 'message' => 'La cédula debe tener entre 6 y 8 caracteres.']);
            return;
        }
        //Validacion de formato de cedula (solo numeros)
        if (!ctype_digit($cedula)) {
            echo json_encode(['success' => false, 'message' => 'La cédula debe contener solo números.']);
            return;
        }
        //Validacion de longitud de usuario
        if (strlen($usuario) < 7) {
            echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 7 caracteres.']);
            return;
        }
        //Validacion de formato de usuario
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            echo json_encode(['success' => false, 'message' => 'El usuario solo puede contener letras y números.']);
            return;
        }
        //Validacion de longitud de contraseña
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }
        //Validacion de complejidad: al menos 1 mayúscula y 1 número
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe contener al menos una mayúscula y un número.']);
            return;
        }
        //Validacion de usuario existente
        if ($this->model->usuarioExists($usuario)) {
            echo json_encode(['success' => false, 'message' => "El usuario '{$usuario}' ya está registrado."]);
            return;
        }
        //Validacion de cedula existente
        if ($this->model->cedulaExists($cedula)) {
            echo json_encode(['success' => false, 'message' => "La cédula 'V-{$cedula}' ya está registrada por otro usuario."]);
            return;
        }
        //Validacion de codigo de operador existente
        if ($codigoOperador && $this->model->codigoExists($codigoOperador)) {
            echo json_encode(['success' => false, 'message' => 'El código de operador ya está en uso.']);
            return;
        }
        //Creacion de usuario
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
            $adminId = (int)$_SESSION['user_id'];
            $this->log->registrar($adminId, 'INSERT', 'usuarios', null, null, [
                'usuario' => $usuario, 
                'nombre_completo' => $nombreCompleto, 
                'cedula' => $cedula, 
                'rol_id' => $rolId
            ], "Usuario '{$usuario}' creado.");
            echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario.']);
        }
    }

    //--------------------------------------------------------------------
    // POST Actualiza la información básica del usuario
    //--------------------------------------------------------------------
    public function update(): void {
        header('Content-Type: application/json');
        //validacion de metodo  
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }
        //Asignacion de variables
        $id             = (int)($_POST['id'] ?? 0);
        $usuario        = trim($_POST['usuario'] ?? '');
        $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
        $cedula         = trim($_POST['cedula'] ?? '');
        $rolId          = (int)($_POST['rol_id'] ?? 0);
        $codigoOperador = trim($_POST['codigo_operador'] ?? '') ?: null;
        //Validacion de campos obligatorios
        if (!$id || empty($usuario) || empty($nombreCompleto) || empty($cedula) || !$rolId) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos.']);
            return;
        }
        //Validacion de longitud de cedula
        if (strlen($cedula) < 6 || strlen($cedula) > 8) {
            echo json_encode(['success' => false, 'message' => 'La cédula debe tener entre 6 y 8 caracteres.']);
            return;
        }
        //Validacion de formato de cedula (solo numeros)
        if (!ctype_digit($cedula)) {
            echo json_encode(['success' => false, 'message' => 'La cédula debe contener solo números.']);
            return;
        }
        //Validacion de usuario existente
        if ($this->model->usuarioExists($usuario, $id)) {
            echo json_encode(['success' => false, 'message' => "El usuario '{$usuario}' ya está registrado por otro usuario."]);
            return;
        }
        //Validacion de cedula existente
        if ($this->model->cedulaExists($cedula, $id)) {
            echo json_encode(['success' => false, 'message' => "La cédula 'V-{$cedula}' ya está registrada por otro usuario."]);
            return;
        }
        //Validacion de codigo de operador existente
        if ($codigoOperador && $this->model->codigoExists($codigoOperador, $id)) {
            echo json_encode(['success' => false, 'message' => 'El código de operador ya está en uso.']);
            return;
        }
        //Actualizacion de usuario
        $data = [
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'usuario'         => $usuario,
            'rol_id'          => $rolId,
            'codigo_operador' => $codigoOperador,
        ];

        $usuarioAnterior = $this->model->getById($id);

        if ($this->model->updateInfo($id, $data)) {
            $adminId = (int)$_SESSION['user_id'];
            $this->log->registrar($adminId, 'UPDATE', 'usuarios', $id, 
                $usuarioAnterior ? [
                    'usuario'         => $usuarioAnterior['usuario'],
                    'nombre_completo' => $usuarioAnterior['nombre_completo'],
                    'cedula'          => $usuarioAnterior['cedula'],
                    'rol_id'          => $usuarioAnterior['rol_id'],
                ] : null,
                [
                    'usuario'         => $usuario,
                    'nombre_completo' => $nombreCompleto,
                    'cedula'          => $cedula,
                    'rol_id'          => $rolId,
                ],
                "Usuario ID {$id} editado."
            );
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario.']);
        }
    }

    //--------------------------------------------------------------------
    // POST Cambia la contraseña de un usuario
    //--------------------------------------------------------------------
    public function updatePassword(): void {
        header('Content-Type: application/json');
        //validacion de metodo
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $newPassword = $_POST['password'] ?? '';
        $confirmPass = $_POST['password_confirm'] ?? '';
        //validacion de campos vacios
        if (!$id || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            return;
        }
        //validacion de longitud de contraseña
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }
        //Validacion de complejidad: al menos 1 mayúscula y 1 número
        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe contener al menos una mayúscula y un número.']);
            return;
        }
        //validacion de que las contraseñas coincidan
        if ($newPassword !== $confirmPass) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            return;
        }
        // Obtener datos antes de cambiar la contraseña
        $usuarioAnterior = $this->model->getById($id);

        // Encriptar la nueva contraseña
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        //actualizacion de contraseña
        if ($this->model->updatePassword($id, $hashed)) {
            $adminId = (int)$_SESSION['user_id'];
            $this->log->registrar($adminId, 'UPDATE', 'usuarios', $id, 
                $usuarioAnterior ? [
                    'usuario'         => $usuarioAnterior['usuario'],
                    'nombre_completo' => $usuarioAnterior['nombre_completo']
                ] : null,
                null, 
                "Contraseña del usuario ID {$id} actualizada."
            );
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.']);
        }
    }

    //--------------------------------------------------------------------
    // POST Alterna el estado activo/inactivo de un usuario
    //--------------------------------------------------------------------
    public function toggleEstado(): void {
        header('Content-Type: application/json');
        //validacion de metodo
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }
        
        $id = (int)($_POST['id'] ?? 0);

        //validacion de campos vacios
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        // Protección: no permitir deshabilitar al propio Super Admin que está logueado
        if ($id === (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio estado.']);
            return;
        }

        // Obtener estado previo antes de cambiarlo
        $usuarioAnterior = $this->model->getById($id);
        $estadoAnterior = $usuarioAnterior ? $usuarioAnterior['estado'] : null;

        $resultado = $this->model->toggleEstado($id);
        //validacion de que el estado se haya actualizado correctamente
        if ($resultado !== false) {
            $adminId     = (int)$_SESSION['user_id'];
            $nuevoEstado = $resultado['nuevo_estado'];
            $this->log->registrar($adminId, 'CAMBIO_ESTADO', 'usuarios', $id, 
                ['estado' => $estadoAnterior], 
                ['estado' => $nuevoEstado], 
                "Usuario ID {$id} cambiado a '{$nuevoEstado}'."
            );
            echo json_encode([
                'success'       => true,
                'message'       => 'Estado actualizado correctamente.',
                'nuevo_estado'  => $nuevoEstado,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        }
    }
}
