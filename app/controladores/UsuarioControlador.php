<?php
/**
 * CONTROLADOR: UsuarioControlador
 * Descripción: Gestiona el ciclo de vida de los usuarios del sistema, incluyendo
 * autenticación, perfiles, permisos y seguridad avanzada (preguntas/respuestas).
 */

use App\modelos\UsuarioModelo;
use App\modelos\RegistroModelo;
use App\Helpers\Validador;
use App\Servicios\UsuarioServicio;

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/RegistroModelo.php';
require_once 'app/Helpers/Validador.php';
require_once 'app/Servicios/UsuarioServicio.php';

class UsuarioControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. SEGURIDAD Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private UsuarioModelo  $modelo;
    private RegistroModelo $modeloRegistro;
    private UsuarioServicio $servicio;

    /**
     * Valida la sesión y los permisos de acceso al módulo antes de instanciar.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id']) || !tienePerm('usuarios', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->modelo          = new UsuarioModelo();
        $this->modeloRegistro  = new RegistroModelo();
        $this->servicio        = new UsuarioServicio();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. RENDERIZADO DE VISTAS
    // ///////////////////////////////////////////////////////////////////

    /**
     * Muestra la interfaz principal de gestión de usuarios.
     */
    public function index(): void {
        try {
            $roles     = $this->modelo->obtenerRoles();
            $preguntas = $this->modeloRegistro->obtenerPreguntasSeguridad();
            
            $tabActiva = $_GET['t'] ?? 'todos';
            $rolActivoId = 0;
            if (strpos($tabActiva, 'rol_') === 0) {
                $rolActivoId = (int)str_replace('rol_', '', $tabActiva);
            }

            require_once 'app/vista/usuarios/index.php';
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en index: " . $e->getMessage());
            die("Ocurrió un error inesperado en el servidor.");
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. MÉTODOS DE CONSULTA (DATATABLES SERVER-SIDE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa la solicitud AJAX para el listado general de usuarios (Server-Side).
     */
    public function obtenerDatos(): void {
        header('Content-Type: application/json');
        session_write_close();
        try {
            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 10;
            $busqueda = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
            $colOrden = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
            $dirOrden = isset($_POST['order'][0]['dir'])    ? $_POST['order'][0]['dir']          : 'asc';

            // El estado determina si listamos usuarios activos o en papelera
            $estado = isset($_GET['estado']) && $_GET['estado'] === 'inactivo' ? 'inactivo' : 'activo';

            $datos          = $this->modelo->obtenerPaginadoUsuarios($inicio, $cantidad, $busqueda, $colOrden, $dirOrden, $estado);
            $totalRegistros = $this->modelo->contarTodosUsuarios($estado);
            $totalFiltrados = $busqueda !== '' ? $this->modelo->contarFiltradosUsuarios($busqueda, $estado) : $totalRegistros;

            echo json_encode([
                'draw'            => $draw,
                'recordsTotal'    => $totalRegistros,
                'recordsFiltered' => $totalFiltrados,
                'data'            => $datos,
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'draw'            => 1,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesa la solicitud AJAX para el listado filtrado por un rol específico.
     */
    public function obtenerDatosPorRol(): void {
        header('Content-Type: application/json');
        session_write_close();
        try {
            $rolId   = (int)($_GET['rol_id'] ?? 0);
            $estado  = $_GET['estado'] ?? 'activo';

            if (!$rolId) {
                echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                return;
            }

            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 10;
            $busqueda = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
            $colOrden = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
            $dirOrden = isset($_POST['order'][0]['dir'])    ? $_POST['order'][0]['dir']          : 'asc';

            $datos          = $this->modelo->obtenerPorRol($rolId, $estado, $inicio, $cantidad, $busqueda, $colOrden, $dirOrden);
            $totalRegistros = $this->modelo->contarPorRol($rolId, $estado);
            $totalFiltrados = $busqueda !== '' ? $this->modelo->contarFiltradosPorRol($rolId, $estado, $busqueda) : $totalRegistros;

            echo json_encode([
                'draw'            => $draw,
                'recordsTotal'    => $totalRegistros,
                'recordsFiltered' => $totalFiltrados,
                'data'            => $datos,
            ]);
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en obtenerDatosPorRol: " . $e->getMessage());
            echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Error inesperado.']);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. OPERACIONES CRUD (CREAR / EDITAR)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida y persiste un nuevo usuario en la base de datos.
     */
    public function guardar(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            // 4.1 Validación de campos obligatorios
            $campos = ['usuario', 'password', 'nombre_completo', 'cedula', 'rol_id'];
            foreach ($campos as $campo) {
                if (empty($_POST[$campo])) {
                    $friendlyNames = ['password' => 'contraseña', 'rol_id' => 'rol'];
                    $nombreCampo = $friendlyNames[$campo] ?? str_replace('_', ' ', $campo);
                    echo json_encode(['success' => false, 'message' => "El campo {$nombreCampo} es obligatorio."]);
                    return;
                }
            }

            // 4.2 Sanitización y validación vía App\Helpers\Validador
            $usuario        = trim($_POST['usuario']);
            $contrasena     = $_POST['password'];
            $nombreCompleto = trim($_POST['nombre_completo']);
            $cedula         = trim($_POST['cedula'] ?? '');
            $rolId          = (int)$_POST['rol_id'];
            $estado         = 'activo';

            $valCedula = Validador::validarCedula($cedula, false);
            if (!$valCedula['valido'] && !empty($cedula)) {
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

            $resultado = $this->servicio->crearUsuario([
                'usuario'         => $usuario,
                'password'        => $contrasena,
                'nombre_completo' => $nombreCompleto,
                'cedula'          => $cedula,
                'rol_id'          => $rolId
            ], (int)$_SESSION['user_id']);

            echo json_encode($resultado);
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en guardar: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    /**
     * Valida y actualiza la información de un usuario existente.
     */
    public function actualizar(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            $id             = (int)($_POST['id'] ?? 0);
            $usuario        = trim($_POST['usuario'] ?? '');
            $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
            $cedula         = trim($_POST['cedula'] ?? '');
            $rolId          = (int)($_POST['rol_id'] ?? 0);

            if (!$id || empty($rolId)) {
                echo json_encode(['success' => false, 'message' => 'El id y rol del usuario son obligatorios.']);
                return;
            }

            // 4. Cédula opcional en edición
            if (!empty($cedula)) {
                $valCedula = Validador::validarCedula($cedula, false);
                if (!$valCedula['valido']) {
                    echo json_encode(['success' => false, 'message' => $valCedula['mensaje']]);
                    return;
                }
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

            $datos = [
                'nombre_completo' => $nombreCompleto,
                'cedula'          => $cedula ?: null,
                'usuario'         => $usuario,
                'rol_id'          => $rolId,
            ];

            $resultado = $this->servicio->actualizarUsuario($id, $datos, (int)$_SESSION['user_id']);
            echo json_encode($resultado);
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en actualizar: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. SEGURIDAD: CONTRASEÑAS Y ESTADOS
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa el cambio de contraseña. Requiere respuestas de seguridad si el afectado es SuperAdmin.
     */
    public function actualizarContrasena(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }

            $id          = (int)($_POST['id'] ?? 0);
            $nuevaContrasena = $_POST['password'] ?? '';
            $confirmarContrasena = $_POST['password_confirm'] ?? '';

            if (!$id || empty($nuevaContrasena)) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
                return;
            }

            $valPass = Validador::validarContrasena($nuevaContrasena);
            if (!$valPass['valido']) {
                echo json_encode(['success' => false, 'message' => $valPass['mensaje']]);
                return;
            }

            if ($nuevaContrasena !== $confirmarContrasena) {
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
                return;
            }

            $resultado = $this->servicio->cambiarContrasena($id, $nuevaContrasena, (int)$_SESSION['user_id'], [
                'ans_1' => trim($_POST['ans_1'] ?? ''),
                'ans_2' => trim($_POST['ans_2'] ?? '')
            ]);

            echo json_encode($resultado);
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en actualizarContrasena: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    /**
     * Alterna el estado de un usuario (Soft Delete). Bloquea la autodesactivación y al SuperAdmin.
     */
    public function alternarEstado(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                return;
            }

            $resultado = $this->servicio->alternarEstado($id, (int)$_SESSION['user_id'], $_SESSION['user_name']);
            echo json_encode($resultado);
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en alternarEstado: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 6. SEGURIDAD: PREGUNTAS DE RECUPERACIÓN
    // ///////////////////////////////////////////////////////////////////

    /**
     * Obtiene los enunciados de las preguntas de seguridad de un usuario específico.
     */
    public function obtenerPreguntasSeguridad(): void {
        header('Content-Type: application/json');
        try {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                return;
            }

            $preguntas = $this->modelo->obtenerPreguntasUsuario($id);
            if ($preguntas) {
                echo json_encode(['success' => true, 'questions' => $preguntas]);
            } else {
                echo json_encode(['success' => false, 'message' => 'El usuario no tiene preguntas configuradas.']);
            }
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en obtenerPreguntasSeguridad: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    /**
     * Actualiza las preguntas de seguridad. Requiere validación mediante el Código de Fábrica.
     */
    public function actualizarPreguntasSeguridad(): void {
        header('Content-Type: application/json');
        try {
            $id          = (int)($_POST['id'] ?? 0);
            $codigoFabrica = trim($_POST['factory_code'] ?? '');
            $p1          = (int)($_POST['pregunta_1'] ?? 0);
            $p2          = (int)($_POST['pregunta_2'] ?? 0);
            $r1          = trim($_POST['respuesta_1'] ?? '');
            $r2          = trim($_POST['respuesta_2'] ?? '');

            if (!$id || empty($codigoFabrica) || !$p1 || !$p2 || empty($r1) || empty($r2)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
                return;
            }

            // 6. Validación del contenido y longitud de respuestas con el Helper
            $valR1 = Validador::validarRespuestaSeguridad($r1);
            if (!$valR1['valido']) { echo json_encode(['success' => false, 'message' => $valR1['mensaje']]); return; }
            $valR2 = Validador::validarRespuestaSeguridad($r2);
            if (!$valR2['valido']) { echo json_encode(['success' => false, 'message' => $valR2['mensaje']]); return; }

            $resultado = $this->servicio->actualizarPreguntas($id, [
                'p1' => $p1, 'p2' => $p2, 'r1' => $r1, 'r2' => $r2
            ], $codigoFabrica, (int)$_SESSION['user_id']);

            echo json_encode($resultado);
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en actualizarPreguntasSeguridad: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }
}
