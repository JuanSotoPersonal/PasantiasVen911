<?php

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/RegistroModelo.php';
require_once 'app/modelos/EventoModelo.php';

use App\modelos\UsuarioModelo;
use App\modelos\RegistroModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;

require_once 'app/Helpers/Validador.php';

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
        try {
        // Si ya hay usuarios, no permitir entrar al setup
        if ($this->usuarioModelo->contarUsuarios() > 0) {
            header('Location: index.php?url=auth');
            exit;
        }

        $preguntas = $this->registroModelo->obtenerPreguntasSeguridad();
        require_once 'app/vista/setup.php';
        } catch (\Exception $e) {
            error_log("[RegistroControlador] Error en index: " . $e->getMessage());
            die("Ocurrió un error inesperado en el servidor.");
        }
    }

    //--------------------------------------------------------------------
    // Procesa el registro del primer SuperAdmin
    //--------------------------------------------------------------------
    public function registrar(): void {
        header('Content-Type: application/json');
        try {

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

        // Validaciones delegadas al Validador
        
        $valCedula = Validador::validarCedula($cedula, true);
        if (!$valCedula['valido']) {
            echo json_encode(['success' => false, 'message' => $valCedula['mensaje']]);
            return;
        }

        $valUsuario = Validador::validarUsuario($usuario);
        if (!$valUsuario['valido']) {
            echo json_encode(['success' => false, 'message' => $valUsuario['mensaje']]);
            return;
        }

        $valNombre = Validador::validarNombreCompleto($nombreCompleto);
        if (!$valNombre['valido']) {
            echo json_encode(['success' => false, 'message' => $valNombre['mensaje']]);
            return;
        }
        
        $valPass = Validador::validarContrasena($contrasena);
        if (!$valPass['valido']) {
            echo json_encode(['success' => false, 'message' => $valPass['mensaje']]);
            return;
        }

        if ($p1 === 0 || $p2 === 0) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar dos preguntas de seguridad diferentes.']);
            return;
        }

        if ($p1 === $p2) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar dos preguntas de seguridad diferentes.']);
            return;
        }

        $valR1 = Validador::validarRespuestaSeguridad($r1);
        if (!$valR1['valido']) {
            echo json_encode(['success' => false, 'message' => $valR1['mensaje']]);
            return;
        }

        $valR2 = Validador::validarRespuestaSeguridad($r2);
        if (!$valR2['valido']) {
            echo json_encode(['success' => false, 'message' => $valR2['mensaje']]);
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
        } catch (\Exception $e) {
            error_log("[RegistroControlador] Error en registrar: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }
}
