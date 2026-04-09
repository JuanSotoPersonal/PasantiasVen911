<?php

require_once 'app/Models/Usuario.php';
require_once 'app/Models/UsuarioModel.php';
require_once 'app/Models/SetupModel.php';
require_once 'app/Models/LogModel.php';

use App\Models\Usuario;
use App\Models\UsuarioModel;
use App\Models\SetupModel;
use App\Models\LogModel;

class SetupController {

    private $userModel;
    private $setupModel;

    public function __construct() {
        $this->userModel = new Usuario(); // Para el conteo inicial
        $this->setupModel = new SetupModel();
    }

    //--------------------------------------------------------------------
    // Muestra la pantalla de registro inicial
    //--------------------------------------------------------------------
    public function index(): void {
        // Si ya hay usuarios, no permitir entrar al setup
        if ($this->userModel->countUsers() > 0) {
            header('Location: index.php?url=auth');
            exit;
        }

        $preguntas = $this->setupModel->getSecurityQuestions();
        require_once 'app/Views/setup.php';
    }

    //--------------------------------------------------------------------
    // Procesa el registro del primer SuperAdmin
    //--------------------------------------------------------------------
    public function register(): void {
        header('Content-Type: application/json');

        if ($this->userModel->countUsers() > 0) {
            echo json_encode(['success' => false, 'message' => 'El sistema ya ha sido inicializado.']);
            return;
        }

        // Validación de Código de Fábrica
        $factoryCode = trim($_POST['factory_code'] ?? '');
        if (!$this->setupModel->validateActivationKey($factoryCode)) {
            echo json_encode(['success' => false, 'message' => 'Código de activación de sistema inválido.']);
            return;
        }

        // Datos del SuperAdmin
        $usuario        = trim($_POST['usuario'] ?? '');
        $password       = $_POST['password'] ?? '';
        $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
        $cedula         = trim($_POST['cedula'] ?? '');
        
        // Datos de Seguridad
        $p1 = (int)($_POST['pregunta_1'] ?? 0);
        $p2 = (int)($_POST['pregunta_2'] ?? 0);
        $r1 = trim($_POST['respuesta_1'] ?? '');
        $r2 = trim($_POST['respuesta_2'] ?? '');

        // Validaciones básicas
        if (empty($usuario) || empty($password) || empty($nombreCompleto) || empty($r1) || empty($r2) || $p1 === 0 || $p2 === 0) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        if ($p1 === $p2) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar dos preguntas de seguridad diferentes.']);
            return;
        }

        // Crear el usuario con UsuarioModel (el que maneja la creación avanzada)
        $fullModel = new UsuarioModel();
        
        $data = [
            'usuario'         => $usuario,
            'password'        => password_hash($password, PASSWORD_DEFAULT),
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'rol_id'          => 1, // SuperAdmin forzado
            'codigo_operador' => 'ROOT-01',
            'estado'          => 'activo',
            'pregunta_1_id'   => $p1,
            'pregunta_2_id'   => $p2,
            'respuesta_1'     => password_hash(strtolower($r1), PASSWORD_DEFAULT), // Hasheadas y en minúsculas
            'respuesta_2'     => password_hash(strtolower($r2), PASSWORD_DEFAULT)
        ];

        if ($fullModel->create($data)) {
            // El primer login es manual tras el setup
            $log = new LogModel();
            $log->registrar(0, 'INSERT', 'usuarios', null, null, ['usuario' => $usuario], "Sistema inicializado con SuperAdmin: {$usuario}.");
            
            echo json_encode(['success' => true, 'message' => 'Sistema activado con éxito. Redirigiendo al login...']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el administrador inicial.']);
        }
    }
}
