<?php

require_once 'app/modelos/UsuarioModelo.php';
require_once 'app/modelos/RegistroModelo.php';
require_once 'app/modelos/EventoModelo.php';
use App\modelos\UsuarioModelo;
use App\modelos\RegistroModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;

require_once 'app/Helpers/Validador.php';

class UsuarioControlador {

    private UsuarioModelo $modelo;

    //--------------------------------------------------------------------
    // Constructor
    //--------------------------------------------------------------------

    private EventoModelo $log;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || !tienePerm('usuarios', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->modelo = new UsuarioModelo();
        $this->log    = new EventoModelo();
    }

    //--------------------------------------------------------------------
    // Muestra la vista principal del módulo de usuarios
    //--------------------------------------------------------------------

    public function index(): void {
        try {
        $roles = $this->modelo->obtenerRoles();
        $registroModelo = new RegistroModelo();
        $preguntas = $registroModelo->obtenerPreguntasSeguridad();
        require_once 'app/vista/usuarios/index.php';
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en index: " . $e->getMessage());
            die("Ocurrió un error inesperado en el servidor.");
        }
    }

    //--------------------------------------------------------------------
    // GET Retorna todos los usuarios en formato JSON (para DataTable)
    //--------------------------------------------------------------------

    public function obtenerDatos(): void {
        header('Content-Type: application/json');
        try {
            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 10;
            $busqueda = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
            $colOrden = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
            $dirOrden = isset($_POST['order'][0]['dir'])    ? $_POST['order'][0]['dir']          : 'asc';

            // El estado lo manda la URL como parámetro GET (?estado=activo|inactivo)
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

    //--------------------------------------------------------------------
    // GET Retorna usuarios de un rol específico en JSON (para DataTables por rol)
    //--------------------------------------------------------------------

    public function obtenerDatosPorRol(): void {
        header('Content-Type: application/json');
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

    //--------------------------------------------------------------------
    // POST Crea un nuevo usuario
    //--------------------------------------------------------------------
    public function guardar(): void {
        header('Content-Type: application/json');
        try {

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

        //Validacion de usuario existente
        if ($this->modelo->existeUsuario($usuario)) {
            echo json_encode(['success' => false, 'message' => "El usuario '{$usuario}' ya está registrado."]);
            return;
        }
        //Validacion de cedula existente
        if ($this->modelo->existeCedula($cedula)) {
            echo json_encode(['success' => false, 'message' => "La cédula 'V-{$cedula}' ya está registrada por otro usuario."]);
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
        $datos = [
            'usuario'         => $usuario,
            'password'        => password_hash($contrasena, PASSWORD_DEFAULT),
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'rol_id'          => $rolId,
            'estado'          => $estado,
            'pregunta_1_id'   => ($rolId === 1) ? $p1 : null,
            'pregunta_2_id'   => ($rolId === 1) ? $p2 : null,
            'respuesta_1'     => ($rolId === 1) ? password_hash(strtolower($r1), PASSWORD_DEFAULT) : null,
            'respuesta_2'     => ($rolId === 1) ? password_hash(strtolower($r2), PASSWORD_DEFAULT) : null,
        ];
        
        if ($this->modelo->crear($datos)) {
            $admin_id = (int)$_SESSION['user_id'];
            $this->log->registrarEvento($admin_id, 'INSERT', 'usuarios', null, null, [
                'usuario' => $usuario, 
                'nombre_completo' => $nombreCompleto, 
                'cedula' => $cedula, 
                'rol_id' => $rolId
            ], "Usuario '{$usuario}' creado.");
            echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario.']);
        }
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en guardar: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    //--------------------------------------------------------------------
    // POST Actualiza la información básica del usuario
    //--------------------------------------------------------------------
    public function actualizar(): void {
        header('Content-Type: application/json');
        try {
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
        if (!$id || empty($rolId)) {
            echo json_encode(['success' => false, 'message' => 'El id y rol del usuario son obligatorios.']);
            return;
        }

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
        //Validacion de usuario existente
        if ($this->modelo->existeUsuario($usuario, $id)) {
            echo json_encode(['success' => false, 'message' => "El usuario '{$usuario}' ya está registrado por otro usuario."]);
            return;
        }
        //Validacion de cedula existente
        if ($this->modelo->existeCedula($cedula, $id)) {
            echo json_encode(['success' => false, 'message' => "la cedula '{$cedula}' ya esta registrada por otro usuario."]);
            return;
        }
        $usuarioAnterior = $this->modelo->obtenerPorId($id);

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
        $datos = [
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $cedula ?: null,
            'usuario'         => $usuario,
            'rol_id'          => $rolId,
        ];

        if ($this->modelo->actualizarInformacion($id, $datos)) {
            $admin_id = (int)$_SESSION['user_id'];
            $this->log->registrarEvento($admin_id, 'UPDATE', 'usuarios', $id, 
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
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en actualizar: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    //--------------------------------------------------------------------
    // POST Cambia la contraseña de un usuario
    //--------------------------------------------------------------------
    public function actualizarContrasena(): void {
        header('Content-Type: application/json');
        try {
        //validacion de metodo
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
        //validacion de que las contraseñas coincidan
        if ($nuevaContrasena !== $confirmarContrasena) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            return;
        }

        // Obtener datos antes de cambiar la contraseña
        $usuarioAnterior = $this->modelo->obtenerPorId($id);

        // EXTRA: Verificación de Seguridad para SuperAdmin
        if ($usuarioAnterior && (int)$usuarioAnterior['rol_id'] === 1) {
            $res1 = trim($_POST['ans_1'] ?? '');
            $res2 = trim($_POST['ans_2'] ?? '');
            
            if (empty($res1) || empty($res2)) {
                echo json_encode(['success' => false, 'message' => 'Debes responder las preguntas de seguridad.']);
                return;
            }

            if (!$this->modelo->verificarRespuestasSeguridad($id, $res1, $res2)) {
                echo json_encode(['success' => false, 'message' => 'Respuestas de seguridad incorrectas. Acceso denegado.']);
                return;
            }
        }

        // Encriptar la nueva contraseña
        $contrasena_hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

        //actualizacion de contraseña
        if ($this->modelo->actualizarContrasena($id, $contrasena_hash)) {
            $admin_id = (int)$_SESSION['user_id'];
            $this->log->registrarEvento($admin_id, 'UPDATE', 'usuarios', $id, 
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
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en actualizarContrasena: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    //--------------------------------------------------------------------
    // POST Alterna el estado activo/inactivo de un usuario
    //--------------------------------------------------------------------
    public function alternarEstado(): void {
        header('Content-Type: application/json');
        try {
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
        $usuarioAfectado = $this->modelo->obtenerPorId($id);
        if ($usuarioAfectado && (int)$usuarioAfectado['rol_id'] === 1) {
            echo json_encode(['success' => false, 'message' => 'El SuperAdministrador no puede ser desactivado.']);
            return;
        }

        // Obtener estado previo antes de cambiarlo
        $usuarioAnterior = $this->modelo->obtenerPorId($id);
        $estadoAnterior = $usuarioAnterior ? $usuarioAnterior['estado'] : null;

        $resultado = $this->modelo->alternarEstado($id);
        //validacion de que el estado se haya actualizado correctamente
        if ($resultado !== false) {
            $admin_id    = (int)$_SESSION['user_id'];
            $nuevoEstado = $resultado['nuevo_estado'];
            $this->log->registrarEvento($admin_id, 'CAMBIO_ESTADO', 'usuarios', $id, 
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
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en alternarEstado: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }

    //--------------------------------------------------------------------
    // GET Obtiene las preguntas de seguridad de un usuario (AJAX)
    //--------------------------------------------------------------------
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

    //--------------------------------------------------------------------
    // POST Actualiza las preguntas de seguridad 
    //--------------------------------------------------------------------
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

        // Validación de longitud de respuestas
        if (strlen($r1) > 128 || strlen($r2) > 128) {
            echo json_encode(['success' => false, 'message' => 'Las respuestas de seguridad no pueden exceder los 128 caracteres.']);
            return;
        }

        // Validar Código de Fábrica
        $registroModelo = new RegistroModelo();
        if (!$registroModelo->validarLlaveActivacion($codigoFabrica)) {
            echo json_encode(['success' => false, 'message' => 'Código de Fábrica inválido. No tienes permiso para esta acción.']);
            return;
        }

        $datos = [
            'pregunta_1_id' => $p1,
            'pregunta_2_id' => $p2,
            'respuesta_1'   => password_hash(strtolower($r1), PASSWORD_DEFAULT),
            'respuesta_2'   => password_hash(strtolower($r2), PASSWORD_DEFAULT)
        ];

        // Ahora usamos el método unificado en el modelo
        if ($this->modelo->actualizarCamposSeguridad($id, $datos)) {
            $admin_id = (int)$_SESSION['user_id'];
            $this->log->registrarEvento($admin_id, 'UPDATE', 'usuarios', $id, null, null, "Preguntas de seguridad del usuario ID {$id} actualizadas.");
            echo json_encode(['success' => true, 'message' => 'Preguntas de seguridad actualizadas correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar las preguntas.']);
        }
        } catch (\Exception $e) {
            error_log("[UsuarioControlador] Error en actualizarPreguntasSeguridad: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }
}
