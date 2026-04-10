<?php

require_once 'app/Models/UsuarioModel.php';
require_once 'app/Models/LogModel.php';
require_once 'app/Models/SetupModel.php';
use App\Models\UsuarioModel;
use App\Models\LogModel;
use App\Models\SetupModel;

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
        $setupModel = new SetupModel();
        $preguntas = $setupModel->getSecurityQuestions();
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
        if (strlen($usuario) > 32) {
            echo json_encode(['success' => false, 'message' => 'El usuario no puede exceder los 32 caracteres.']);
            return;
        }
        //Validacion de formato de usuario
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            echo json_encode(['success' => false, 'message' => 'El usuario solo puede contener letras y números.']);
            return;
        }
        //Validacion de longitud de nombre completo
        if (strlen($nombreCompleto) > 128) {
            echo json_encode(['success' => false, 'message' => 'El nombre completo no puede exceder los 128 caracteres.']);
            return;
        }
        //Validacion de codigo de operador
        if ($codigoOperador && strlen($codigoOperador) > 128) {
            echo json_encode(['success' => false, 'message' => 'El código de operador no puede exceder los 128 caracteres.']);
            return;
        }
        //Validacion de longitud de contraseña
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }
        if (strlen($password) > 128) {
            echo json_encode(['success' => false, 'message' => 'La contraseña no puede exceder los 128 caracteres.']);
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

        // RESTRICCIÓN: Solo un SuperAdmin
        if ($rolId === 1) {
            echo json_encode(['success' => false, 'message' => 'Solo puede existir un SuperAdministrador en el sistema.']);
            return;
        }
        // Atributos de seguridad si es SuperAdmin (Rol 1)
        $p1 = (int)($_POST['pregunta_1'] ?? 0);
        $p2 = (int)($_POST['pregunta_2'] ?? 0);
        $r1 = trim($_POST['respuesta_1'] ?? '');
        $r2 = trim($_POST['respuesta_2'] ?? '');

        if ($rolId === 1) {
            if ($p1 === 0 || $p2 === 0 || empty($r1) || empty($r2)) {
                echo json_encode(['success' => false, 'message' => 'Los SuperAdministradores deben configurar sus preguntas de seguridad.']);
                return;
            }
            if (strlen($r1) > 128 || strlen($r2) > 128) {
                echo json_encode(['success' => false, 'message' => 'Las respuestas de seguridad no pueden exceder los 128 caracteres.']);
                return;
            }
            if ($p1 === $p2) {
                echo json_encode(['success' => false, 'message' => 'Debes elegir dos preguntas diferentes.']);
                return;
            }
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
            'pregunta_1_id'   => ($rolId === 1) ? $p1 : null,
            'pregunta_2_id'   => ($rolId === 1) ? $p2 : null,
            'respuesta_1'     => ($rolId === 1) ? password_hash(strtolower($r1), PASSWORD_DEFAULT) : null,
            'respuesta_2'     => ($rolId === 1) ? password_hash(strtolower($r2), PASSWORD_DEFAULT) : null,
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
        if (strlen($usuario) > 32) {
            echo json_encode(['success' => false, 'message' => 'El usuario no puede exceder los 32 caracteres.']);
            return;
        }
        if (strlen($nombreCompleto) > 128) {
            echo json_encode(['success' => false, 'message' => 'El nombre completo no puede exceder los 128 caracteres.']);
            return;
        }
        if ($codigoOperador && strlen($codigoOperador) > 128) {
            echo json_encode(['success' => false, 'message' => 'El código de operador no puede exceder los 128 caracteres.']);
            return;
        }

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
        $usuarioAnterior = $this->model->getById($id);

        // RESTRICCIÓN: SuperAdmin Único
        if ($usuarioAnterior) {
            $oldRol = (int)$usuarioAnterior['rol_id'];
            
            // 1. No se puede promover a nadie a Rol 1
            if ($rolId === 1 && $oldRol !== 1) {
                echo json_encode(['success' => false, 'message' => 'No se puede promover a un usuario al rol de SuperAdministrador.']);
                return;
            }
            
            // 2. El SuperAdmin no puede degradarse a sí mismo
            if ($oldRol === 1 && $rolId !== 1) {
                echo json_encode(['success' => false, 'message' => 'El SuperAdministrador no puede cambiar su propio rol.']);
                return;
            }
        }

        //Actualizacion de usuario
        $data = [
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'usuario'         => $usuario,
            'rol_id'          => $rolId,
            'codigo_operador' => $codigoOperador,
        ];

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
        if (strlen($newPassword) > 128) {
            echo json_encode(['success' => false, 'message' => 'La contraseña no puede exceder los 128 caracteres.']);
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

        // EXTRA: Verificación de Seguridad para SuperAdmin
        if ($usuarioAnterior && (int)$usuarioAnterior['rol_id'] === 1) {
            $ans1 = trim($_POST['ans_1'] ?? '');
            $ans2 = trim($_POST['ans_2'] ?? '');
            
            if (empty($ans1) || empty($ans2)) {
                echo json_encode(['success' => false, 'message' => 'Debes responder las preguntas de seguridad.']);
                return;
            }

            if (!$this->model->verifySecurityAnswers($id, $ans1, $ans2)) {
                echo json_encode(['success' => false, 'message' => 'Respuestas de seguridad incorrectas. Acceso denegado.']);
                return;
            }
        }

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

        // Protección: no permitir deshabilitar al propio usuario logueado
        if ($id === (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio estado.']);
            return;
        }

        // RESTRICCIÓN: No se puede desactivar al SuperAdmin
        $usuarioAfectado = $this->model->getById($id);
        if ($usuarioAfectado && (int)$usuarioAfectado['rol_id'] === 1) {
            echo json_encode(['success' => false, 'message' => 'El SuperAdministrador no puede ser desactivado.']);
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

    //--------------------------------------------------------------------
    // GET Obtiene las preguntas de seguridad de un usuario (AJAX)
    //--------------------------------------------------------------------
    public function getSecurityQuestions(): void {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $questions = $this->model->getUserQuestions($id);
        if ($questions) {
            echo json_encode(['success' => true, 'questions' => $questions]);
        } else {
            echo json_encode(['success' => false, 'message' => 'El usuario no tiene preguntas configuradas.']);
        }
    }

    //--------------------------------------------------------------------
    // POST Actualiza las preguntas de seguridad (Requiere Código de Fábrica)
    //--------------------------------------------------------------------
    public function updateSecurityQuestions(): void {
        header('Content-Type: application/json');
        $id          = (int)($_POST['id'] ?? 0);
        $factoryCode = trim($_POST['factory_code'] ?? '');
        $p1          = (int)($_POST['pregunta_1'] ?? 0);
        $p2          = (int)($_POST['pregunta_2'] ?? 0);
        $r1          = trim($_POST['respuesta_1'] ?? '');
        $r2          = trim($_POST['respuesta_2'] ?? '');

        if (!$id || empty($factoryCode) || !$p1 || !$p2 || empty($r1) || empty($r2)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        // Validación de longitud de respuestas
        if (strlen($r1) > 128 || strlen($r2) > 128) {
            echo json_encode(['success' => false, 'message' => 'Las respuestas de seguridad no pueden exceder los 128 caracteres.']);
            return;
        }

        // Validar Código de Fábrica
        $setupModel = new SetupModel();
        if (!$setupModel->validateActivationKey($factoryCode)) {
            echo json_encode(['success' => false, 'message' => 'Código de Fábrica inválido. No tienes permiso para esta acción.']);
            return;
        }

        $data = [
            'pregunta_1_id' => $p1,
            'pregunta_2_id' => $p2,
            'respuesta_1'   => password_hash(strtolower($r1), PASSWORD_DEFAULT),
            'respuesta_2'   => password_hash(strtolower($r2), PASSWORD_DEFAULT)
        ];

        // Ahora usamos el método unificado en el modelo
        if ($this->model->updateSecurityFields($id, $data)) {
            $adminId = (int)$_SESSION['user_id'];
            $this->log->registrar($adminId, 'UPDATE', 'usuarios', $id, null, null, "Preguntas de seguridad del usuario ID {$id} actualizadas.");
            echo json_encode(['success' => true, 'message' => 'Preguntas de seguridad actualizadas correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar las preguntas.']);
        }
    }
}
