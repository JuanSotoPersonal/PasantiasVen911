<?php
/**
 * CONTROLADOR: FichaControlador
 * Propósito: Gestionar el flujo operativo de las Fichas de Emergencia.
 * Maneja la recepción de incidentes, asignación de estados y administración de catálogos.
 */

require_once 'app/modelos/FichaModelo.php';
require_once 'app/modelos/EventoModelo.php';
require_once 'app/Helpers/Validador.php';
require_once 'app/Helpers/Notificador.php';

use App\modelos\FichaModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;
use App\Helpers\Notificador;

class FichaControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private FichaModelo  $modelo;
    private EventoModelo $modeloEvento;

    /**
     * Valida la sesión activa e instancia los modelos de operación y auditoría.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        $this->modelo       = new FichaModelo();
        $this->modeloEvento = new EventoModelo();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. RENDERIZADO DE VISTAS (INDEX)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Despliega la interfaz principal del módulo de fichas con datos precargados.
     */
    public function index(): void {
        if (!tienePerm('fichas', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }

        $tabActiva   = $_GET['t'] ?? 'todas';
        $usuarioId   = (int)$_SESSION['user_id'];
        $rolId       = (int)$_SESSION['user_rol_id'];

        // 2.1 Carga de catálogos para optimizar la experiencia en modales
        $municipios      = $this->modelo->obtenerMunicipios();
        $tiposEmergencia = $this->modelo->obtenerTiposEmergencia();

        require_once 'app/vista/fichas/index.php';
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. MÉTODOS DE CONSULTA (DATATABLES SERVER-SIDE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa las solicitudes de DataTables para el listado de emergencias.
     * Soporta filtrado por estado y restricciones de visibilidad por rol.
     */
    public function obtenerDatos(): void {
        header('Content-Type: application/json');
        try {
            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 10;
            $busqueda = trim($_POST['search']['value'] ?? '');
            $colOrden = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 5;
            $dirOrden = $_POST['order'][0]['dir'] ?? 'desc';

            $estado    = $_GET['estado'] ?? 'todos';
            $usuarioId = (int)$_SESSION['user_id'];
            $rolId     = (int)$_SESSION['user_rol_id'];

            $datos          = $this->modelo->obtenerPaginado($inicio, $cantidad, $busqueda, $colOrden, $dirOrden, $estado, $usuarioId, $rolId);
            $totalRegistros = $this->modelo->contarTodos($estado, $usuarioId, $rolId);
            $totalFiltrados = $busqueda !== '' ? $this->modelo->contarFiltrados($busqueda, $estado, $usuarioId, $rolId) : $totalRegistros;

            echo json_encode([
                'draw'            => $draw,
                'recordsTotal'    => $totalRegistros,
                'recordsFiltered' => $totalFiltrados,
                'data'            => $datos,
            ]);
        } catch (\Exception $e) {
            echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. OPERACIONES CRUD (CREAR / EDITAR)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida y registra una nueva ficha de emergencia.
     * Implementa blindaje contra campos vacíos y formatos inválidos.
     */
    public function guardar(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('fichas', 'crear')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para crear fichas.']);
                return;
            }

            $datos = [
                'parroquia_id'       => (int)($_POST['parroquia_id'] ?? 0),
                'direccion_exacta'   => trim($_POST['direccion_exacta'] ?? ''),
                'caso_id'            => (int)($_POST['caso_id'] ?? 0),
                'descripcion_caso'   => trim($_POST['descripcion_caso'] ?? ''),
                'nombre_solicitante' => trim($_POST['nombre_solicitante'] ?? ''),
                'cedula_solicitante' => trim($_POST['cedula_solicitante'] ?? ''),
                'telefono1'          => trim($_POST['telefono1'] ?? ''),
                'telefono2'          => trim($_POST['telefono2'] ?? ''),
                'id_user'            => (int)$_SESSION['user_id'],
            ];

            // 4.1 Pre-validación táctica de presencia de datos
            if ($datos['nombre_solicitante'] === '') { echo json_encode(['success' => false, 'message' => 'El Nombre Completo es obligatorio.']); return; }
            if ($datos['telefono1'] === '')          { echo json_encode(['success' => false, 'message' => 'El Teléfono de Contacto 1 es obligatorio.']); return; }
            if ($datos['parroquia_id'] === 0)        { echo json_encode(['success' => false, 'message' => 'Debe seleccionar una Parroquia válida.']); return; }
            if ($datos['direccion_exacta'] === '')   { echo json_encode(['success' => false, 'message' => 'La Dirección Exacta es obligatoria.']); return; }
            if ($datos['caso_id'] === 0)             { echo json_encode(['success' => false, 'message' => 'Debe seleccionar un Caso Específico.']); return; }
            if ($datos['descripcion_caso'] === '')   { echo json_encode(['success' => false, 'message' => 'La Descripción del Caso es obligatoria.']); return; }

            // 4.2 Validación de formatos delegada al Helper Validador
            $valCedula = Validador::validarCedula($datos['cedula_solicitante'], false);
            if (!$valCedula['valido']) { echo json_encode(['success' => false, 'message' => $valCedula['mensaje']]); return; }

            $valNombre = Validador::validarNombreCompleto($datos['nombre_solicitante']);
            if (!$valNombre['valido']) { echo json_encode(['success' => false, 'message' => $valNombre['mensaje']]); return; }

            $valTel1 = Validador::validarTelefono($datos['telefono1']);
            if (!$valTel1['valido']) { echo json_encode(['success' => false, 'message' => $valTel1['mensaje']]); return; }

            $valTel2 = Validador::validarTelefono($datos['telefono2'], false);
            if (!$valTel2['valido']) { echo json_encode(['success' => false, 'message' => $valTel2['mensaje']]); return; }

            $valParroquia = Validador::validarId($datos['parroquia_id'], 'Parroquia');
            if (!$valParroquia['valido']) { echo json_encode(['success' => false, 'message' => $valParroquia['mensaje']]); return; }

            $valDireccion = Validador::validarTextoLibre($datos['direccion_exacta'], 'Dirección Exacta', 10, 500);
            if (!$valDireccion['valido']) { echo json_encode(['success' => false, 'message' => $valDireccion['mensaje']]); return; }

            $valCaso = Validador::validarId($datos['caso_id'], 'Caso Específico');
            if (!$valCaso['valido']) { echo json_encode(['success' => false, 'message' => $valCaso['mensaje']]); return; }

            $valDesc = Validador::validarTextoLibre($datos['descripcion_caso'], 'Resumen Técnico de la Situación', 10, 1000);
            if (!$valDesc['valido']) { echo json_encode(['success' => false, 'message' => $valDesc['mensaje']]); return; }

            // 4.3 Persistencia y Auditoría de creación
            $fichaId = $this->modelo->crear($datos);
            if (!$fichaId) {
                echo json_encode(['success' => false, 'message' => 'No se pudo registrar la ficha.']);
                return;
            }

            $this->modeloEvento->registrarEventoFicha(
                $fichaId,
                (int)$_SESSION['user_id'],
                'CREACION',
                null,
                'Pendiente',
                null,
                ['id' => $fichaId, 'caso' => $datos['caso_id'], 'estado' => 'Pendiente'],
                "Ficha de emergencia #{$fichaId} creada."
            );

            // ///////////////////////////////////////////////////////////////////
            // NOTIFICACIONES EN TIEMPO REAL (Enrutamiento por Roles)
            // ///////////////////////////////////////////////////////////////////

            // 1. A Despacho (Rol 3): Alerta inmediata de nueva emergencia para despacho
            Notificador::enviarPorRol(3, 'alerta', 'Nueva Emergencia', "Se ha generado la Ficha #{$fichaId}. Requiere atención inmediata.", $fichaId);
            
            // 2. A Jefatura (Rol 4): Notificación de auditoría de creación
            Notificador::enviarPorRol(4, 'info', 'Nueva Ficha Registrada', "El Operador {$_SESSION['user_name']} ha registrado la Ficha #{$fichaId}.", $fichaId);

            // 3. Al Administrador (Rol 1): Visibilidad global del sistema
            Notificador::enviarPorRol(1, 'info', 'Sistema: Registro de Ficha', "Se ha creado la Ficha de Emergencia #{$fichaId}.", $fichaId);

            echo json_encode(['success' => true, 'message' => "Ficha #{$fichaId} registrada correctamente.", 'id' => $fichaId]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    /**
     * Valida y actualiza una ficha existente. 
     * Implementa restricción de inmutabilidad para fichas cerradas/finalizadas.
     */
    public function actualizar(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('fichas', 'editar')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para editar fichas.']);
                return;
            }

            $fichaId = (int)($_POST['ficha_id'] ?? 0);
            $datos = [
                'parroquia_id'       => (int)($_POST['parroquia_id'] ?? 0),
                'direccion_exacta'   => trim($_POST['direccion_exacta'] ?? ''),
                'caso_id'            => (int)($_POST['caso_id'] ?? 0),
                'descripcion_caso'   => trim($_POST['descripcion_caso'] ?? ''),
                'nombre_solicitante' => trim($_POST['nombre_solicitante'] ?? ''),
                'cedula_solicitante' => trim($_POST['cedula_solicitante'] ?? ''),
                'telefono1'          => trim($_POST['telefono1'] ?? ''),
                'telefono2'          => trim($_POST['telefono2'] ?? ''),
            ];

            $valFichaId = Validador::validarId($fichaId, 'ID de Ficha');
            if (!$valFichaId['valido']) { echo json_encode(['success' => false, 'message' => $valFichaId['mensaje']]); return; }

            $anterior = $this->modelo->obtenerPorId($fichaId);
            if (!$anterior) {
                echo json_encode(['success' => false, 'message' => 'Ficha no encontrada.']);
                return;
            }
            
            // 4.4 Blindaje Inmutable: No se editan registros cerrados o atendidos (estados terminales)
            if (in_array($anterior['estado_ficha'], ['Cerrado', 'Atendido'])) {
                echo json_encode(['success' => false, 'message' => 'No se puede editar una emergencia en estado terminal (Cerrado o Atendido).']);
                return;
            }

            if ($datos['nombre_solicitante'] === '') { echo json_encode(['success' => false, 'message' => 'El Nombre Completo es obligatorio.']); return; }
            if ($datos['telefono1'] === '')          { echo json_encode(['success' => false, 'message' => 'El Teléfono de Contacto 1 es obligatorio.']); return; }
            if ($datos['parroquia_id'] === 0)        { echo json_encode(['success' => false, 'message' => 'Debe seleccionar una Parroquia válida.']); return; }
            if ($datos['direccion_exacta'] === '')   { echo json_encode(['success' => false, 'message' => 'La Dirección Exacta es obligatoria.']); return; }
            if ($datos['caso_id'] === 0)             { echo json_encode(['success' => false, 'message' => 'Debe seleccionar un Caso Específico.']); return; }
            if ($datos['descripcion_caso'] === '')   { echo json_encode(['success' => false, 'message' => 'La Descripción del Caso es obligatoria.']); return; }

            $valCedula = Validador::validarCedula($datos['cedula_solicitante'], false);
            if (!$valCedula['valido']) { echo json_encode(['success' => false, 'message' => $valCedula['mensaje']]); return; }
            $valNombre = Validador::validarNombreCompleto($datos['nombre_solicitante']);
            if (!$valNombre['valido']) { echo json_encode(['success' => false, 'message' => $valNombre['mensaje']]); return; }
            $valTel1 = Validador::validarTelefono($datos['telefono1']);
            if (!$valTel1['valido']) { echo json_encode(['success' => false, 'message' => $valTel1['mensaje']]); return; }
            $valTel2 = Validador::validarTelefono($datos['telefono2'], false);
            if (!$valTel2['valido']) { echo json_encode(['success' => false, 'message' => $valTel2['mensaje']]); return; }
            $valParroquia = Validador::validarId($datos['parroquia_id'], 'Parroquia');
            if (!$valParroquia['valido']) { echo json_encode(['success' => false, 'message' => $valParroquia['mensaje']]); return; }
            $valDireccion = Validador::validarTextoLibre($datos['direccion_exacta'], 'Dirección Exacta', 10, 500);
            if (!$valDireccion['valido']) { echo json_encode(['success' => false, 'message' => $valDireccion['mensaje']]); return; }
            $valCaso = Validador::validarId($datos['caso_id'], 'Caso Específico');
            if (!$valCaso['valido']) { echo json_encode(['success' => false, 'message' => $valCaso['mensaje']]); return; }
            $valDesc = Validador::validarTextoLibre($datos['descripcion_caso'], 'Resumen Técnico de la Situación', 10, 1000);
            if (!$valDesc['valido']) { echo json_encode(['success' => false, 'message' => $valDesc['mensaje']]); return; }

            $exito = $this->modelo->actualizar($fichaId, $datos, (int)$_SESSION['user_id']);

            if ($exito) {
                $this->modeloEvento->registrarEventoFicha(
                    $fichaId,
                    (int)$_SESSION['user_id'],
                    'MODIFICACION',
                    $anterior['estado_ficha'],
                    $anterior['estado_ficha'],
                    $anterior,
                    $datos,
                    "Ficha #{$fichaId} actualizada."
                );
                echo json_encode(['success' => true, 'message' => "Ficha #{$fichaId} actualizada."]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la ficha.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. GESTIÓN DE ESTADOS Y DETALLES
    // ///////////////////////////////////////////////////////////////////

    /**
     * Cambia el estado de una ficha (Pendiente -> En Proceso -> etc).
     */
    public function cambiarEstado(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('fichas', 'cambiar_estado')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para cambiar estado.']);
                return;
            }

            $fichaId      = (int)($_POST['ficha_id']     ?? 0);
            $nuevoEstado  = trim($_POST['nuevo_estado']  ?? '');
            $motivoCierre = trim($_POST['motivo_cierre'] ?? '');

            // Hallazgo 1 — Validación formal del ID y whitelist de estados permitidos
            $valFichaId = Validador::validarId($fichaId, 'ID de Ficha');
            if (!$valFichaId['valido']) {
                echo json_encode(['success' => false, 'message' => $valFichaId['mensaje']]);
                return;
            }

            $estadosPermitidos = ['Pendiente', 'En Proceso', 'Atendido', 'Cerrado'];
            if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
                echo json_encode(['success' => false, 'message' => 'El estado especificado no es válido.']);
                return;
            }

            // Al cerrar una ficha, el motivo es obligatorio
            if ($nuevoEstado === 'Cerrado' && $motivoCierre === '') {
                echo json_encode(['success' => false, 'message' => 'Debe ingresar el motivo de cierre de la ficha.']);
                return;
            }

            // Validar longitud y contenido del motivo si se proporciona
            if ($motivoCierre !== '') {
                $valMotivo = Validador::validarTextoLibre($motivoCierre, 'Motivo de Cierre', 5, 500);
                if (!$valMotivo['valido']) {
                    echo json_encode(['success' => false, 'message' => $valMotivo['mensaje']]);
                    return;
                }
            }

            $anterior = $this->modelo->obtenerPorId($fichaId);
            if (!$anterior) {
                echo json_encode(['success' => false, 'message' => 'Ficha no encontrada.']);
                return;
            }

            // Blindaje de estados terminales
            if (in_array($anterior['estado_ficha'], ['Cerrado', 'Atendido'])) {
                echo json_encode(['success' => false, 'message' => 'No se puede modificar el estado de una ficha en estado terminal.']);
                return;
            }

            $exito = $this->modelo->cambiarEstado($fichaId, $nuevoEstado, (int)$_SESSION['user_id'], $motivoCierre);

            if ($exito) {
                // Incluir el motivo de cierre en la descripción del evento de auditoría
                $descripcionEvento = "Ficha #{$fichaId} cambió de '{$anterior['estado_ficha']}' a '{$nuevoEstado}'.";
                if ($motivoCierre !== '') {
                    $descripcionEvento .= " Motivo: {$motivoCierre}";
                }

                $this->modeloEvento->registrarEventoFicha(
                    $fichaId,
                    (int)$_SESSION['user_id'],
                    'CAMBIO_ESTADO',
                    $anterior['estado_ficha'],
                    $nuevoEstado,
                    ['estado' => $anterior['estado_ficha']],
                    ['estado' => $nuevoEstado, 'motivo' => $motivoCierre],
                    $descripcionEvento
                );

                // ///////////////////////////////////////////////////////////////////
                // NOTIFICACIONES EN TIEMPO REAL (Enrutamiento por Roles)
                // ///////////////////////////////////////////////////////////////////

                // 1. Al Operador (Rol 2): Feedback sobre el estado de su ficha creada
                if (isset($anterior['id_user'])) {
                    Notificador::enviarAUsuario(
                        (int)$anterior['id_user'], 
                        'cambio_estado', 
                        'Estado de Ficha Actualizado', 
                        "Tu Ficha #{$fichaId} ha cambiado de '{$anterior['estado_ficha']}' a '{$nuevoEstado}'.", 
                        $fichaId
                    );
                }

                // 2. A Jefatura (Rol 4): Seguimiento de flujo operativo
                Notificador::enviarPorRol(4, 'info', 'Actualización de Emergencia', "La Ficha #{$fichaId} fue actualizada a '{$nuevoEstado}' por {$_SESSION['user_name']}.", $fichaId);

                // 3. Al Administrador (Rol 1): Supervisión global de cambios de estado
                Notificador::enviarPorRol(1, 'info', 'Sistema: Cambio de Estado', "Ficha #{$fichaId} cambió a '{$nuevoEstado}'.", $fichaId);

                echo json_encode(['success' => true, 'message' => "Estado actualizado a '{$nuevoEstado}'.", 'nuevo_estado' => $nuevoEstado]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    /**
     * Retorna el detalle completo de una ficha para visualización en modal.
     */
    public function detalle(): void {
        header('Content-Type: application/json');
        try {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false]); return; }
            $ficha = $this->modelo->obtenerPorId($id);
            echo json_encode(['success' => (bool)$ficha, 'data' => $ficha]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 6. AJAX — CONFIGURACIÓN Y CATÁLOGOS (SOLO ADMIN)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna parroquias filtradas por municipio para selectores dinámicos.
     */
    public function obtenerParroquiasPorMunicipio(): void {
        header('Content-Type: application/json');
        $municipioId = (int)($_GET['municipio_id'] ?? 0);
        echo json_encode($this->modelo->obtenerParroquias($municipioId ?: null));
    }

    /**
     * Retorna casos específicos filtrados por tipo de emergencia.
     */
    public function obtenerCasosPorTipo(): void {
        header('Content-Type: application/json');
        $tipoId = (int)($_GET['tipo_id'] ?? 0);
        echo json_encode($this->modelo->obtenerCasos($tipoId ?: null));
    }

    /**
     * Orquestador central para la gestión de catálogos (CRUD de Tipos, Casos, Municipios, etc).
     */
    public function guardarCatalogo(): void {
        header('Content-Type: application/json');
        try {
            if (!tienePerm('configuracion', 'gestionar')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos.']);
                return;
            }
            $catalogo = $_POST['catalogo'] ?? '';
            $accion   = $_POST['accion']   ?? '';
            $id       = (int)($_POST['id'] ?? 0);

            $resultado = match ($catalogo) {
                'tipo_emergencia' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $nombre = trim($_POST['nombre'] ?? '');
                        $v = Validador::validarNombreCatalogo($nombre, 'Nombre del Tipo');
                        if (!$v['valido']) return $v;
                        return ($accion === 'crear') ? $this->modelo->crearTipoEmergencia($nombre) : $this->modelo->actualizarTipoEmergencia($id, $nombre);
                    })(),
                    'eliminar'  => $this->modelo->toggleEstadoTipoEmergencia($id),
                    default     => false,
                },
                'caso' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $tipoId = (int)($_POST['tipo_emergencia_id'] ?? 0);
                        $nombre = trim($_POST['nombre_caso'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        $vId = Validador::validarId($tipoId, 'Tipo de Emergencia');
                        if (!$vId['valido']) return $vId;
                        $vNom = Validador::validarNombreCatalogo($nombre, 'Nombre del Caso');
                        if (!$vNom['valido']) return $vNom;
                        return ($accion === 'crear') ? $this->modelo->crearCaso($tipoId, $nombre, $desc) : $this->modelo->actualizarCaso($id, $tipoId, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoCaso($id),
                    default    => false,
                },
                'municipio' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $nombre = trim($_POST['nombre_municipio'] ?? $_POST['nombre'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        $v = Validador::validarNombreAlfabetico($nombre, 'Municipio', 30);
                        if (!$v['valido']) return ['valido' => false, 'mensaje' => $v['mensaje']];
                        $vDesc = Validador::validarTextoLibre($desc, 'Descripción', 0, 256);
                        if (!$vDesc['valido']) return ['valido' => false, 'mensaje' => $vDesc['mensaje']];
                        return ($accion === 'crear') ? $this->modelo->crearMunicipio($nombre, $desc) : $this->modelo->actualizarMunicipio($id, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoMunicipio($id),
                    default    => false,
                },
                'parroquia' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $munId  = (int)($_POST['municipio_id'] ?? 0);
                        $nombre = trim($_POST['nombre_parroquia'] ?? $_POST['nombre'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        $vId = Validador::validarId($munId, 'Municipio');
                        if (!$vId['valido']) return $vId;
                        $vNom = Validador::validarNombreCatalogo($nombre, 'Nombre de la Parroquia');
                        if (!$vNom['valido']) return $vNom;
                        return ($accion === 'crear') ? $this->modelo->crearParroquia($munId, $nombre, $desc) : $this->modelo->actualizarParroquia($id, $munId, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoParroquia($id),
                    default    => false,
                },
                'organismo' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $nombre = trim($_POST['nombre_organismo'] ?? $_POST['nombre'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        $v = Validador::validarNombreCatalogo($nombre, 'Nombre del Organismo');
                        if (!$v['valido']) return $v;
                        if (!empty($desc)) {
                            $vDesc = Validador::validarTextoLibre($desc, 'Descripción', 0, 256);
                            if (!$vDesc['valido']) return ['valido' => false, 'mensaje' => $vDesc['mensaje']];
                        }
                        return ($accion === 'crear') ? $this->modelo->crearOrganismo($nombre, $desc) : $this->modelo->actualizarOrganismo($id, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoOrganismo($id),
                    default    => false,
                },
                'motivo_cierre' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $nombre   = trim($_POST['nombre']    ?? '');
                        $desc     = trim($_POST['descripcion'] ?? '');
                        // Contexto validado contra whitelist — 'ficha' por defecto
                        $contexto = in_array($_POST['contexto'] ?? '', ['ficha', 'organismo'])
                            ? $_POST['contexto']
                            : 'ficha';

                        $vNom = Validador::validarNombreCatalogo($nombre, 'Nombre del Motivo');
                        if (!$vNom['valido']) return $vNom;
                        if (!empty($desc)) {
                            $vDesc = Validador::validarTextoLibre($desc, 'Descripción', 0, 300);
                            if (!$vDesc['valido']) return $vDesc;
                        }
                        return ($accion === 'crear')
                            ? $this->modelo->crearMotivoCierre($nombre, $desc, $contexto)
                            : $this->modelo->actualizarMotivoCierre($id, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoMotivoCierre($id),
                    default    => false,
                },
                default => false,
            };

            if (is_array($resultado) && isset($resultado['valido']) && !$resultado['valido']) {
                echo json_encode(['success' => false, 'message' => $resultado['mensaje']]);
                return;
            }

            echo json_encode([
                'success' => (bool)$resultado, 
                'message' => $resultado ? 'Operación exitosa.' : 'No se pudo completar la operación. Verifique los datos o si ya existe un registro similar.'
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Retorna los datos crudos de cualquier catálogo para el renderizado de tablas administrativas.
     */
    public function obtenerCatalogo(): void {
        header('Content-Type: application/json');
        try {
            if (!tienePerm('configuracion', 'gestionar')) {
                echo json_encode(['data' => []]);
                return;
            }
            $catalogo    = $_GET['cat']          ?? '';
            $tipoId      = (int)($_GET['tipo_id']      ?? 0);
            $municipioId = (int)($_GET['municipio_id'] ?? 0);
            $estado      = (int)($_GET['estado'] ?? 1);
            // Contexto diferenciador para motivos de cierre: 'ficha' o 'organismo'
            $contexto    = in_array($_GET['contexto'] ?? '', ['ficha', 'organismo']) ? $_GET['contexto'] : 'ficha';

            $datos = match ($catalogo) {
                'tipo_emergencia' => $this->modelo->obtenerTiposEmergencia($estado),
                'caso'            => $this->modelo->obtenerCasos($tipoId ?: null, $estado),
                'municipio'       => $this->modelo->obtenerMunicipios($estado),
                'parroquia'       => $this->modelo->obtenerParroquias($municipioId ?: null, $estado),
                'organismo'       => $this->modelo->obtenerOrganismos($estado),
                'motivo_cierre'   => $this->modelo->obtenerMotivosCierre($estado, $contexto),
                default           => [],
            };

            echo json_encode(['data' => $datos]);
        } catch (\Exception $e) {
            error_log("[FichaControlador] Error en obtenerCatalogo: " . $e->getMessage());
            echo json_encode(['data' => []]);
        }
    }
}
