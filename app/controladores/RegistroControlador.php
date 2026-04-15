<?php

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/RegistroModelo.php';
require_once 'app/modelos/EventoModelo.php';

use App\modelos\UsuarioModelo;
use App\modelos\RegistroModelo;
use App\modelos\EventoModelo;

class RegistroControlador {

    private $usuarioModelo;
    private $registroModelo;

    public function __construct() {
        $this->usuarioModelo = new UsuarioModelo(); // Ahora usa el modelo consolidado
        $this->registroModelo = new RegistroModelo();
    }

    //--------------------------------------------------------------------
    // Muestra la pantalla de registro inicial
    //--------------------------------------------------------------------
    public function index(): void {
        // Si ya hay usuarios, no permitir entrar al setup
        if ($this->usuarioModelo->contarUsuarios() > 0) {
            header('Location: index.php?url=auth');
            exit;
        }

        $preguntas = $this->registroModelo->obtenerPreguntasSeguridad();
        require_once 'app/vista/setup.php';
    }

    //--------------------------------------------------------------------
    // Procesa el registro del primer SuperAdmin
    //--------------------------------------------------------------------
    public function registrar(): void {
        header('Content-Type: application/json');

        if ($this->usuarioModelo->contarUsuarios() > 0) {
            echo json_encode(['success' => false, 'message' => 'El sistema ya ha sido inicializado.']);
            return;
        }

        // Validación de Código de Fábrica
        $codigoFabrica = trim($_POST['factory_code'] ?? '');
        if (!$this->registroModelo->validarLlaveActivacion($codigoFabrica)) {
            echo json_encode(['success' => false, 'message' => 'Código de activación de sistema inválido.']);
            return;
        }

        // Datos del SuperAdmin
        $usuario        = trim($_POST['usuario'] ?? '');
        $contrasena     = $_POST['password'] ?? '';
        $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
        $cedula         = trim($_POST['cedula'] ?? '');
        
        // Datos de Seguridad
        $p1 = (int)($_POST['pregunta_1'] ?? 0);
        $p2 = (int)($_POST['pregunta_2'] ?? 0);
        $r1 = trim($_POST['respuesta_1'] ?? '');
        $r2 = trim($_POST['respuesta_2'] ?? '');

        // Validaciones básicas
        if (empty($usuario) || empty($contrasena) || empty($nombreCompleto) || empty($r1) || empty($r2) || $p1 === 0 || $p2 === 0) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        if ($p1 === $p2) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar dos preguntas de seguridad diferentes.']);
            return;
        }

        // Validación de Cédula (6 a 8 dígitos numéricos)
        if (strlen($cedula) < 6 || strlen($cedula) > 8) {
            echo json_encode(['success' => false, 'message' => 'La cédula debe tener entre 6 y 8 caracteres.']);
            return;
        }
        if (!ctype_digit($cedula)) {
            echo json_encode(['success' => false, 'message' => 'La cédula debe contener solo números.']);
            return;
        }

        // Validación de Usuario (Mínimo 7 caracteres, alfanumérico)
        if (strlen($usuario) < 7) {
            echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 7 caracteres.']);
            return;
        }
        if (strlen($usuario) > 32) {
            echo json_encode(['success' => false, 'message' => 'El usuario no puede exceder los 32 caracteres.']);
            return;
        }
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            echo json_encode(['success' => false, 'message' => 'El usuario solo puede contener letras y números.']);
            return;
        }

        // Validación de Nombre Completo
        if (strlen($nombreCompleto) > 128) {
            echo json_encode(['success' => false, 'message' => 'el nombre completo no puede exceder los 128 caracteres.']);
            return;
        }

        // Validación de respuestas de seguridad (Alfanumérico)
        if (strlen($r1) > 128 || strlen($r2) > 128) {
            echo json_encode(['success' => false, 'message' => 'Las respuestas de seguridad no pueden exceder los 128 caracteres.']);
            return;
        }
        if (!preg_match('/^[a-zA-Z0-9 ]+$/', $r1) || !preg_match('/^[a-zA-Z0-9 ]+$/', $r2)) {
            echo json_encode(['success' => false, 'message' => 'Las respuestas de seguridad solo pueden contener letras, números y espacios.']);
            return;
        }

        // Validación de longitud de contrasena (Ya añadida previamente)
        if (strlen($contrasena) < 8) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.']);
            return;
        }
        if (strlen($contrasena) > 128) {
            echo json_encode(['success' => false, 'message' => 'La contraseña no puede exceder los 128 caracteres.']);
            return;
        }
        if (!preg_match('/[A-Z]/', $contrasena) || !preg_match('/[0-9]/', $contrasena)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe contener al menos una mayúscula y un número.']);
            return;
        }

        // Crear el usuario con el modelo consolidado
        $datos = [
            'usuario'         => $usuario,
            'password'        => password_hash($contrasena, PASSWORD_DEFAULT),
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'rol_id'          => 1, // SuperAdmin forzado
            'estado'          => 'activo',
            'pregunta_1_id'   => $p1,
            'pregunta_2_id'   => $p2,
            'respuesta_1'     => password_hash(strtolower($r1), PASSWORD_DEFAULT),
            'respuesta_2'     => password_hash(strtolower($r2), PASSWORD_DEFAULT)
        ];

        if ($this->usuarioModelo->crear($datos)) {
            // El primer login es manual tras el setup (no hay sesión: usuario_id = null)
            $evento = new EventoModelo();
            $evento->registrarEvento(null, 'INSERT', 'usuarios', null, null, ['usuario' => $usuario], "Sistema inicializado con SuperAdmin: {$usuario}.");
            
            echo json_encode(['success' => true, 'message' => 'Sistema activado con éxito. Redirigiendo al login...']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el administrador inicial.']);
        }
    }
}
