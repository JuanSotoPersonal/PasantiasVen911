<?php
/**
 * CONTROLADOR: DespachoControlador
 * Propósito: Gestionar el flujo operativo del Centro de Despacho.
 *
 * FLUJO:
 * 1. index()                  → Vista principal con tabla de fichas activas (global)
 * 2. obtenerDatos()           → DataTable server-side de fichas Pendiente + En Proceso
 * 3. tomarFicha()             → Despachador asume una ficha; id_owner y estado actualizados
 * 4. detalleFicha()           → JSON completo de una ficha + sus despachos asignados
 * 5. guardar()                → Registra un nuevo despacho sobre una ficha En Proceso
 * 6. actualizar()             → Edita datos de campo de un despacho existente
 * 7. cambiarEstado()          → Avanza el estatus del despacho
 * 8. obtenerOrganismos()      → Catálogo de organismos para selectores
 */

require_once 'app/modelos/DespachoModelo.php';
require_once 'app/modelos/FichaModelo.php';
require_once 'app/modelos/EventoModelo.php';
require_once 'app/Helpers/Validador.php';
require_once 'app/Helpers/Notificador.php';

use App\modelos\DespachoModelo;
use App\modelos\FichaModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;
use App\Helpers\Notificador;


class DespachoControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private DespachoModelo $modelo;
    private EventoModelo   $modeloEvento;
    private FichaModelo    $modeloFicha;

    /**
     * Valida sesión activa e instancia los modelos necesarios.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        $this->modelo       = new DespachoModelo();
        $this->modeloEvento = new EventoModelo();
        $this->modeloFicha  = new FichaModelo();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. VISTA PRINCIPAL
    // ///////////////////////////////////////////////////////////////////

    /**
     * Renderiza la interfaz principal del Centro de Despacho.
     * Requiere permiso mínimo de visualización del módulo.
     */
    public function index(): void {
        if (!tienePerm('despachos', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }

        $tabActiva = $_GET['t'] ?? 'general';

        // 2.1 PROTECCIÓN RBAC: Jefatura no puede ver "Mis Fichas" en Despacho (t=propias)
        if ($tabActiva === 'propias' && (int)$_SESSION['user_rol_id'] === 4) {
            header('Location: index.php?url=despacho&t=general');
            exit;
        }

        // Carga de catálogos para alimentar el modal de edición completa de fichas
        // (reutiliza FichaModelo para mantener la lógica centralizada)
        $modeloFicha     = new FichaModelo();
        $municipios      = $modeloFicha->obtenerMunicipios();
        $tiposEmergencia = $modeloFicha->obtenerTiposEmergencia();

        require_once 'app/vista/despachador/index.php';
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. DATATABLE SERVER-SIDE (FICHAS ACTIVAS)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa solicitudes de DataTables para el listado de fichas activas.
     * Retorna fichas en estado Pendiente y En Proceso para TODOS los despachadores.
     * La tabla es global: sin filtro por usuario, para permitir relevo de turnos.
     */
    public function obtenerDatos(): void {
        header('Content-Type: application/json');
        session_write_close();
        try {
            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 15;
            $busqueda = trim($_POST['search']['value'] ?? '');
            $colOrden = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 6;
            $dirOrden = $_POST['order'][0]['dir'] ?? 'asc';

            $datos          = $this->modelo->obtenerFichasPaginado($inicio, $cantidad, $busqueda, $colOrden, $dirOrden);
            $totalRegistros = $this->modelo->contarFichas();
            $totalFiltrados = $busqueda !== '' ? $this->modelo->contarFichasFiltradas($busqueda) : $totalRegistros;

            echo json_encode([
                'draw'            => $draw,
                'recordsTotal'    => $totalRegistros,
                'recordsFiltered' => $totalFiltrados,
                'data'            => $datos,
            ]);
        } catch (\Exception $e) {
            echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. DATATABLE SERVER-SIDE (FICHAS PROPIAS DEL DESPACHADOR)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna fichas activas donde id_owner = usuario en sesión.
     * Permite al despachador ver únicamente las fichas que tomó en su turno.
     */
    public function obtenerDatosPropios(): void {
        header('Content-Type: application/json');
        session_write_close();

        // 4.1 PROTECCIÓN RBAC: Jefatura no posee fichas propias (proceso de despacho)
        if ((int)$_SESSION['user_rol_id'] === 4) {
            echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
            return;
        }

        try {
            $draw      = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio    = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad  = isset($_POST['length']) ? (int)$_POST['length'] : 15;
            $busqueda  = trim($_POST['search']['value'] ?? '');
            $colOrden  = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 6;
            $dirOrden  = $_POST['order'][0]['dir'] ?? 'asc';
            $usuarioId = (int)$_SESSION['user_id'];

            $datos          = $this->modelo->obtenerFichasPropiasPaginado($usuarioId, $inicio, $cantidad, $busqueda, $colOrden, $dirOrden);
            $totalRegistros = $this->modelo->contarFichasPropias($usuarioId);
            $totalFiltrados = $busqueda !== '' ? $this->modelo->contarFichasPropiasFiltradas($usuarioId, $busqueda) : $totalRegistros;

            echo json_encode([
                'draw'            => $draw,
                'recordsTotal'    => $totalRegistros,
                'recordsFiltered' => $totalFiltrados,
                'data'            => $datos,
            ]);
        } catch (\Exception $e) {
            echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. TOMAR FICHA
    // ///////////////////////////////////////////////////////////////////

    /**
     * El despachador asume la responsabilidad de una ficha.
     * Actualiza id_owner al usuario actual y cambia estado a "En Proceso".
     * Esta operación es segura para cambio de turno: cualquier despachador puede
     * tomar una ficha que estaba siendo atendida por otro (solo actualiza el owner).
     */
    public function tomarFicha(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('despachos', 'crear')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para tomar fichas.']);
                return;
            }

            $fichaId   = (int)($_POST['ficha_id'] ?? 0);
            $valFicha  = Validador::validarId($fichaId, 'Ficha de Emergencia');
            if (!$valFicha['valido']) {
                echo json_encode(['success' => false, 'message' => $valFicha['mensaje']]);
                return;
            }

            // 4.1 Verificar que la ficha exista y esté en estado operable
            $infoFicha = $this->modelo->obtenerInfoFicha($fichaId);
            if (!$infoFicha) {
                echo json_encode(['success' => false, 'message' => 'Ficha no encontrada.']);
                return;
            }
            if (!in_array($infoFicha['estado_ficha'], ['Pendiente', 'En Proceso'], true)) {
                echo json_encode(['success' => false, 'message' => "La ficha está en estado '{$infoFicha['estado_ficha']}' y no puede ser tomada."]);
                return;
            }

            $usuarioId      = (int)$_SESSION['user_id'];
            $estadoAnterior = $infoFicha['estado_ficha'];
            $exito          = $this->modelo->tomarFicha($fichaId, $usuarioId);

            if ($exito) {
                // 4.2 Auditoría: registrar el evento de toma de ficha
                $this->modeloEvento->registrarEventoFicha(
                    $fichaId,
                    $usuarioId,
                    'CAMBIO_ESTADO',
                    $estadoAnterior,
                    'En Proceso',
                    null,
                    ['id_owner' => $usuarioId],
                    "Ficha tomada por despachador. Estado: '{$estadoAnterior}' → 'En Proceso'."
                );
                // NOTIFICACIONES: Al operador creador de la ficha + Jefatura + Admin
                if (!empty($infoFicha['id_user'])) {
                    Notificador::enviarAUsuario(
                        (int)$infoFicha['id_user'],
                        'info',
                        'Ficha en Proceso',
                        "Tu Ficha #{$fichaId} ha pasado de 'Pendiente' a 'En Proceso' y está siendo atendida por {$_SESSION['user_name']}.",
                        $fichaId
                    );
                }
                Notificador::enviarPorRol(4, 'info', 'Ficha Tomada: Inicio de Gestión', "El despachador {$_SESSION['user_name']} ha tomado la Ficha #{$fichaId}, pasando su estado de 'Pendiente' a 'En Proceso'.", $fichaId);
                Notificador::enviarPorRol(1, 'info', 'Auditoría: Ficha Tomada', "Ficha #{$fichaId} tomada por {$_SESSION['user_name']}. Estado: Pendiente → En Proceso.", $fichaId);

                echo json_encode(['success' => true, 'message' => "Ficha #{$fichaId} tomada correctamente.", 'ficha_id' => $fichaId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo tomar la ficha. Es posible que ya no esté disponible.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. DETALLE DE FICHA (PARA EL MODAL DE GESTIÓN)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna el detalle completo de una ficha junto con sus despachos asignados.
     * Usado para cargar el modal de gestión de despachos.
     */
    public function detalleFicha(): void {
        header('Content-Type: application/json');
        session_write_close();
        try {
            $fichaId = (int)($_GET['id'] ?? 0);
            if (!$fichaId) {
                echo json_encode(['success' => false, 'message' => 'ID de ficha inválido.']);
                return;
            }

            $ficha     = $this->modelo->obtenerFichaPorId($fichaId);
            $despachos = $this->modelo->obtenerDespachosDeFicha($fichaId);

            echo json_encode([
                'success'   => (bool)$ficha,
                'ficha'     => $ficha,
                'despachos' => $despachos,
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 6. CREACIÓN DE DESPACHO (ASIGNAR ORGANISMO)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida y registra un nuevo despacho (organismo asignado) sobre una ficha.
     * La ficha debe estar en estado "En Proceso" para poder recibir despachos.
     */
    public function guardar(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('despachos', 'crear')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para crear despachos.']);
                return;
            }

            $fichaId         = (int)($_POST['ficha_id']       ?? 0);
            $organismoId     = (int)($_POST['organismo_id']   ?? 0);
            $unidadDesignada = trim($_POST['unidad_designada'] ?? '');
            $mandoAcargo     = trim($_POST['mando_acargo']    ?? '');
            $personaAtiende  = trim($_POST['persona_atiende']  ?? '');

            $valFicha     = Validador::validarId($fichaId,    'Ficha de Emergencia');
            $valOrganismo = Validador::validarId($organismoId, 'Organismo');
            $valUnidad    = Validador::validarTextoLibre($unidadDesignada, 'Unidad Designada', 2, 100);
            $valMando     = Validador::validarTextoLibre($mandoAcargo,     'Mando a Cargo',     2, 100);

            if (!$valFicha['valido'])     { echo json_encode(['success' => false, 'message' => $valFicha['mensaje']]);    return; }
            if (!$valOrganismo['valido']) { echo json_encode(['success' => false, 'message' => $valOrganismo['mensaje']]); return; }
            if (!$valUnidad['valido'])    { echo json_encode(['success' => false, 'message' => $valUnidad['mensaje']]);   return; }
            if (!$valMando['valido'])     { echo json_encode(['success' => false, 'message' => $valMando['mensaje']]);    return; }

            if ($personaAtiende !== '') {
                $valPersona = Validador::validarTextoLibre($personaAtiende, 'Persona que Atiende', 2, 100);
                if (!$valPersona['valido']) { echo json_encode(['success' => false, 'message' => $valPersona['mensaje']]); return; }
            }

            // 6.1 Validar que la ficha esté en estado "En Proceso"
            $infoFicha = $this->modelo->obtenerInfoFicha($fichaId);
            if (!$infoFicha || $infoFicha['estado_ficha'] !== 'En Proceso') {
                $estado = $infoFicha['estado_ficha'] ?? 'desconocido';
                echo json_encode(['success' => false, 'message' => "Solo se pueden asignar organismos a fichas En Proceso. Estado actual: '{$estado}'."]);
                return;
            }

            $usuarioId  = (int)$_SESSION['user_id'];
            $despachoId = $this->modelo->crear([
                'ficha_id'        => $fichaId,
                'organismo_id'    => $organismoId,
                'unidad_designada' => $unidadDesignada,
                'mando_acargo'    => $mandoAcargo,
                'persona_atiende' => $personaAtiende ?: null,
                'despachador_id'  => $usuarioId,
            ]);

            if (!$despachoId) {
                echo json_encode(['success' => false, 'message' => 'No se pudo registrar el despacho.']);
                return;
            }

            // 6.2 Auditoría del evento de despacho en la trazabilidad de la ficha
            $this->modeloEvento->registrarEventoFicha(
                $fichaId,
                $usuarioId,
                'DESPACHO',
                null, null, null,
                ['despacho_id' => $despachoId, 'organismo_id' => $organismoId, 'unidad' => $unidadDesignada],
                "Despacho #{$despachoId}: Organismo ID {$organismoId} — Unidad '{$unidadDesignada}'."
            );

            // NOTIFICACIONES: Al operador creador + Jefatura + Admin
            if (!empty($infoFicha['id_user'])) {
                Notificador::enviarAUsuario(
                    (int)$infoFicha['id_user'],
                    'info',
                    'Organismo Despachado',
                    "Se ha despachado un organismo a tu Ficha #{$fichaId}.",
                    $fichaId
                );
            }
            Notificador::enviarPorRol(4, 'alerta', 'Nuevo Despacho de Organismo', "Organismo asignado a la Ficha #{$fichaId}. Despacho #{$despachoId}.", $fichaId);
            Notificador::enviarPorRol(1, 'info', 'Sistema: Organismo Despachado', "Despacho #{$despachoId} registrado en Ficha #{$fichaId}.", $fichaId);

            echo json_encode(['success' => true, 'message' => "Despacho registrado correctamente.", 'id' => $despachoId]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 7. CAMBIO DE ESTATUS DEL DESPACHO
    // ///////////////////////////////////////////////////////////////////

    /**
     * Avanza el estatus de un despacho: Asignado → En Camino → En Sitio → Liberado.
     * El estado "Liberado" es terminal e irreversible.
     */
    public function cambiarEstado(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('despachos', 'cambiar_estado')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para cambiar estado de despachos.']);
                return;
            }

            $despachoId  = (int)($_POST['despacho_id'] ?? 0);
            $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');

            if (!$despachoId || $nuevoEstado === '') {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
                return;
            }

            $anterior = $this->modelo->obtenerPorId($despachoId);
            if (!$anterior) {
                echo json_encode(['success' => false, 'message' => 'Despacho no encontrado.']);
                return;
            }
            if ($anterior['estatus_despacho'] === 'Liberado') {
                echo json_encode(['success' => false, 'message' => 'Este despacho ya fue Liberado y no puede ser modificado.']);
                return;
            }

            $exito = $this->modelo->cambiarEstado($despachoId, $nuevoEstado);
            if ($exito) {
                $this->modeloEvento->registrarEventoFicha(
                    (int)$anterior['ficha_id'],
                    (int)$_SESSION['user_id'],
                    'DESPACHO',
                    $anterior['estatus_despacho'],
                    $nuevoEstado,
                    ['estatus' => $anterior['estatus_despacho']],
                    ['estatus' => $nuevoEstado],
                    "Despacho #{$despachoId}: '{$anterior['estatus_despacho']}' → '{$nuevoEstado}'."
                );
                // NOTIFICACIONES: Jefatura + Admin (el cambio de estatus de organismo es auditoría operativa)
                $fichaIdDespacho = (int)$anterior['ficha_id'];
                Notificador::enviarPorRol(4, 'info', 'Avance de Organismo', "Despacho #{$despachoId}: '{$anterior['estatus_despacho']}' → '{$nuevoEstado}' en Ficha #{$fichaIdDespacho}.", $fichaIdDespacho);
                Notificador::enviarPorRol(1, 'info', 'Sistema: Avance Despacho', "Despacho #{$despachoId} cambió a '{$nuevoEstado}'.", $fichaIdDespacho);

                echo json_encode(['success' => true, 'message' => "Estatus actualizado a '{$nuevoEstado}'.", 'nuevo_estado' => $nuevoEstado]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo cambiar el estatus.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 7b. CANCELAR DESPACHO DE ORGANISMO
    //     Cancela un organismo despachado que aún no ha sido Liberado.
    //     Requiere un motivo seleccionado del catálogo y descripción libre.
    // ///////////////////////////////////////////////////////////////////

    /**
     * Cancela un organismo despachado activo.
     * Solo se permite si el estatus actual es Asignado, En Camino o En Sitio.
     * El motivo proviene del catálogo de Motivos de Cierre (reutilizado).
     */
    public function cancelarDespacho(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('despachos', 'cambiar_estado')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para cancelar despachos.']);
                return;
            }

            $despachoId  = (int)($_POST['despacho_id']  ?? 0);
            $tipoMotivo  = trim($_POST['tipo_motivo']   ?? '');
            $descripcion = trim($_POST['descripcion']   ?? '');

            // Validación formal de campos obligatorios
            $valId = Validador::validarId($despachoId, 'Despacho');
            if (!$valId['valido']) {
                echo json_encode(['success' => false, 'message' => $valId['mensaje']]);
                return;
            }

            $valTipo = Validador::validarNombreCatalogo($tipoMotivo, 'Tipo de Motivo');
            if (!$valTipo['valido']) {
                echo json_encode(['success' => false, 'message' => $valTipo['mensaje']]);
                return;
            }

            if ($descripcion !== '') {
                $valDesc = Validador::validarTextoLibre($descripcion, 'Descripción', 3, 500);
                if (!$valDesc['valido']) {
                    echo json_encode(['success' => false, 'message' => $valDesc['mensaje']]);
                    return;
                }
            }

            // Verificar que el despacho exista y sea cancelable
            $despacho = $this->modelo->obtenerPorId($despachoId);
            if (!$despacho) {
                echo json_encode(['success' => false, 'message' => 'Despacho no encontrado.']);
                return;
            }
            if (in_array($despacho['estatus_despacho'], ['Liberado', 'Cancelado'], true)) {
                echo json_encode(['success' => false, 'message' => "El despacho ya está en estado terminal ('{$despacho['estatus_despacho']}') y no puede cancelarse."]);
                return;
            }

            $exito = $this->modelo->cancelar($despachoId, $tipoMotivo, $descripcion);

            if ($exito) {
                // Auditoría del evento de cancelación
                $this->modeloEvento->registrarEventoFicha(
                    (int)$despacho['ficha_id'],
                    (int)$_SESSION['user_id'],
                    'DESPACHO',
                    $despacho['estatus_despacho'],
                    'Cancelado',
                    ['estatus' => $despacho['estatus_despacho']],
                    ['estatus' => 'Cancelado', 'motivo' => "{$tipoMotivo}: {$descripcion}"],
                    "Despacho #{$despachoId} ({$despacho['nombre_organismo']}) cancelado. Motivo: {$tipoMotivo}."
                );
                // NOTIFICACIONES: Jefatura + Admin
                $fichaIdCancelacion = (int)$despacho['ficha_id'];
                Notificador::enviarPorRol(4, 'alerta', 'Organismo Cancelado', "El organismo '{$despacho['nombre_organismo']}' fue cancelado en Ficha #{$fichaIdCancelacion}. Motivo: {$tipoMotivo}.", $fichaIdCancelacion);
                Notificador::enviarPorRol(1, 'info', 'Sistema: Despacho Cancelado', "Despacho #{$despachoId} ({$despacho['nombre_organismo']}) cancelado.", $fichaIdCancelacion);

                echo json_encode(['success' => true, 'message' => "Despacho de '{$despacho['nombre_organismo']}' cancelado correctamente."]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo cancelar el despacho.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 8. EDITAR FICHA OPERACIONALMENTE (DESDE EL MODAL DE DESPACHO)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Actualiza los campos operacionales de una ficha: descripcion, dirección y teléfonos.
     * Solo el Despachador y el Administrador tienen acceso a esta acción.
     * No requiere selectores en cascada: edita únicamente datos de texto operativos.
     */
    public function editarFicha(): void {
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

            $fichaId     = (int)($_POST['ficha_id']         ?? 0);
            $descripcion = trim($_POST['descripcion_caso']   ?? '');
            $direccion   = trim($_POST['direccion_exacta']   ?? '');
            $telefono1   = trim($_POST['telefono1']          ?? '');
            $telefono2   = trim($_POST['telefono2']          ?? '');

            $valFicha     = Validador::validarId($fichaId, 'Ficha de Emergencia');
            $valDescripcion = Validador::validarTextoLibre($descripcion, 'Descripción del Caso', 5, 2000);
            $valDireccion   = Validador::validarTextoLibre($direccion,   'Dirección Exacta',     5, 500);
            $valTel1        = Validador::validarTelefono($telefono1, true);

            if (!$valFicha['valido'])      { echo json_encode(['success' => false, 'message' => $valFicha['mensaje']]);      return; }
            if (!$valDescripcion['valido']) { echo json_encode(['success' => false, 'message' => $valDescripcion['mensaje']]); return; }
            if (!$valDireccion['valido'])   { echo json_encode(['success' => false, 'message' => $valDireccion['mensaje']]);   return; }
            if (!$valTel1['valido'])        { echo json_encode(['success' => false, 'message' => $valTel1['mensaje']]);        return; }

            if ($telefono2 !== '') {
                $valTel2 = Validador::validarTelefono($telefono2, false);
                if (!$valTel2['valido']) { echo json_encode(['success' => false, 'message' => $valTel2['mensaje']]); return; }
            }

            // Verificar que la ficha exista y sea operable
            $infoFicha = $this->modelo->obtenerInfoFicha($fichaId);
            if (!$infoFicha || $infoFicha['estado_ficha'] === 'Cerrado') {
                echo json_encode(['success' => false, 'message' => 'La ficha ya está cerrada y no puede modificarse.']);
                return;
            }

            $usuarioId = (int)$_SESSION['user_id'];
            $exito     = $this->modelo->actualizarFichaOperacional($fichaId, [
                'descripcion_caso' => $descripcion,
                'direccion_exacta' => $direccion,
                'telefono1'        => $telefono1,
                'telefono2'        => $telefono2 ?: null,
            ], $usuarioId);

            if ($exito) {
                $this->modeloEvento->registrarEventoFicha(
                    $fichaId, $usuarioId, 'MODIFICACION', null, null, null,
                    ['descripcion' => $descripcion, 'direccion' => $direccion],
                    "Ficha #{$fichaId} editada desde Centro de Despacho por usuario ID {$usuarioId}."
                );
                echo json_encode(['success' => true, 'message' => "Ficha #{$fichaId} actualizada correctamente."]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudieron guardar los cambios.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 9. CAMBIAR ESTADO DE FICHA (SOLO DESPACHADOR / ADMIN)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Gestiona la transición de estados de una ficha desde el Centro de Despacho.
     * El Operador (Rol 2) NO puede usar este endpoint — bloqueado por RBAC.
     * Registra id_owner = usuario actual en cada transición.
     *
     * Flujo: Pendiente → En Proceso → Atendido → Cerrado
     */
    public function cambiarEstadoFicha(): void {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
                return;
            }
            if (!tienePerm('fichas', 'cambiar_estado')) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos para cambiar el estado de fichas.']);
                return;
            }

            $fichaId      = (int)($_POST['ficha_id']     ?? 0);
            $nuevoEstado  = trim($_POST['nuevo_estado']  ?? '');
            $motivoCierre = trim($_POST['motivo_cierre'] ?? '');
            $tipoMotivo   = trim($_POST['tipo_motivo'] ?? '');

            // Hallazgo 1/2/3 — Validación formal del ID, whitelist de estados y motivos
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

            // Al cerrar una ficha, el motivo y el tipo son obligatorios
            if ($nuevoEstado === 'Cerrado' && ($motivoCierre === '' || $tipoMotivo === '')) {
                echo json_encode(['success' => false, 'message' => 'Debe ingresar el tipo y el motivo de cierre de la ficha.']);
                return;
            }

            // Hallazgo 2: Validar contenido y longitud de motivo_cierre con el Helper
            if ($motivoCierre !== '') {
                $valMotivo = Validador::validarTextoLibre($motivoCierre, 'Motivo de Cierre', 5, 500);
                if (!$valMotivo['valido']) {
                    echo json_encode(['success' => false, 'message' => $valMotivo['mensaje']]);
                    return;
                }
            }

            // Hallazgo 3: Validar tipo_motivo como nombre de catálogo (longitud y caracteres)
            if ($tipoMotivo !== '') {
                $valTipo = Validador::validarNombreCatalogo($tipoMotivo, 'Tipo de Motivo');
                if (!$valTipo['valido']) {
                    echo json_encode(['success' => false, 'message' => $valTipo['mensaje']]);
                    return;
                }
            }

            $infoFicha = $this->modelo->obtenerInfoFicha($fichaId);
            if (!$infoFicha) {
                echo json_encode(['success' => false, 'message' => 'Ficha no encontrada.']);
                return;
            }

            // Atendido y Cerrado son ambos estados terminales
            if (in_array($infoFicha['estado_ficha'], ['Cerrado', 'Atendido'], true)) {
                echo json_encode(['success' => false, 'message' => "La ficha ya está en estado terminal '{$infoFicha['estado_ficha']}' y no puede ser modificada."]);
                return;
            }

            // Blindaje de integridad: verificar que no haya organismos despachados sin resolver (Liberado/Cancelado)
            if (in_array($nuevoEstado, ['Cerrado', 'Atendido'])) {
                $despachosActivos = $this->modelo->contarDespachosActivos($fichaId);
                if ($despachosActivos > 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => "No se puede marcar como {$nuevoEstado}: hay {$despachosActivos} organismo(s) en curso. Libere o cancele todos los organismos antes de finalizar.",
                    ]);
                    return;
                }
            }

            $usuarioId      = (int)$_SESSION['user_id'];
            $estadoAnterior = $infoFicha['estado_ficha'];

            // Delegar al FichaModelo centralizado (gestiona hora_cierre, motivo_cierre y tipo_motivo_cierre)
            $exito = $this->modeloFicha->cambiarEstado($fichaId, $nuevoEstado, $usuarioId, $motivoCierre, $tipoMotivo);

            if ($exito) {
                $descripcion = "Estado cambiado desde Centro de Despacho: '{$estadoAnterior}' → '{$nuevoEstado}'.";
                if ($motivoCierre !== '') {
                    $descripcion .= " Motivo: {$motivoCierre}";
                }

                $this->modeloEvento->registrarEventoFicha(
                    $fichaId, $usuarioId, 'CAMBIO_ESTADO',
                    $estadoAnterior, $nuevoEstado,
                    ['estado' => $estadoAnterior],
                    ['estado' => $nuevoEstado, 'motivo' => $motivoCierre],
                    $descripcion
                );
                // NOTIFICACIONES: Al operador creador de la ficha + Jefatura + Admin
                if (!empty($infoFicha['id_user'])) {
                    Notificador::enviarAUsuario(
                        (int)$infoFicha['id_user'],
                        'cambio_estado',
                        'Estado de Ficha Actualizado',
                        "Tu Ficha #{$fichaId} cambió de '{$estadoAnterior}' a '{$nuevoEstado}' por {$_SESSION['user_name']}.",
                        $fichaId
                    );
                }
                Notificador::enviarPorRol(4, 'info', 'Actualización de Emergencia', "La Ficha #{$fichaId} fue actualizada a '{$nuevoEstado}' por {$_SESSION['user_name']}.", $fichaId);
                Notificador::enviarPorRol(1, 'info', 'Sistema: Cambio de Estado', "Ficha #{$fichaId} cambió a '{$nuevoEstado}'.", $fichaId);

                echo json_encode(['success' => true, 'message' => "Estado de la ficha actualizado a '{$nuevoEstado}'.", 'nuevo_estado' => $nuevoEstado]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }


    // ///////////////////////////////////////////////////////////////////
    // 10. CATÁLOGO DE ORGANISMOS
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna el catálogo de organismos activos para el selector del modal.
     */
    public function obtenerOrganismos(): void {
        header('Content-Type: application/json');
        session_write_close();
        try {
            echo json_encode($this->modelo->obtenerOrganismos());
        } catch (\Exception $e) {
            echo json_encode([]);
        }
    }
}

