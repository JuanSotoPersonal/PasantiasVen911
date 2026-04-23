<?php

require_once 'app/modelos/FichaModelo.php';
require_once 'app/modelos/EventoModelo.php';
use App\modelos\FichaModelo;
use App\modelos\EventoModelo;
use App\Helpers\Validador;

require_once 'app/Helpers/Validador.php';

class FichaControlador {

    private FichaModelo  $modelo;
    private EventoModelo $modeloEvento;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        $this->modelo       = new FichaModelo();
        $this->modeloEvento = new EventoModelo();
    }

    //--------------------------------------------------------------------
    // GET Muestra la vista principal del módulo de fichas
    //--------------------------------------------------------------------
    public function index(): void {
        if (!tienePerm('fichas', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }

        $tabActiva   = $_GET['t'] ?? 'todas';
        $usuarioId   = (int)$_SESSION['user_id'];
        $rolId       = (int)$_SESSION['user_rol_id'];

        // Pre-cargar datos de configuración para los selectores en modales
        $municipios      = $this->modelo->obtenerMunicipios();
        $tiposEmergencia = $this->modelo->obtenerTiposEmergencia();

        require_once 'app/vista/fichas/index.php';
    }

    //--------------------------------------------------------------------
    // POST Retorna fichas paginadas en JSON para DataTables (server-side)
    //--------------------------------------------------------------------
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

    //--------------------------------------------------------------------
    // POST Crea una nueva ficha de emergencia
    //--------------------------------------------------------------------
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

            $valParroquia = Validador::validarId($datos['parroquia_id'], 'Parroquia');
            if (!$valParroquia['valido']) { echo json_encode(['success' => false, 'message' => $valParroquia['mensaje']]); return; }

            $valCaso = Validador::validarId($datos['caso_id'], 'Caso');
            if (!$valCaso['valido']) { echo json_encode(['success' => false, 'message' => $valCaso['mensaje']]); return; }

            $valDireccion = Validador::validarTextoLibre($datos['direccion_exacta'], 'Dirección Exacta', 10, 500);
            if (!$valDireccion['valido']) { echo json_encode(['success' => false, 'message' => $valDireccion['mensaje']]); return; }

            $valDesc = Validador::validarTextoLibre($datos['descripcion_caso'], 'Descripción del Caso', 10, 1000);
            if (!$valDesc['valido']) { echo json_encode(['success' => false, 'message' => $valDesc['mensaje']]); return; }

            $valNombre = Validador::validarNombreCompleto($datos['nombre_solicitante']);
            if (!$valNombre['valido']) { echo json_encode(['success' => false, 'message' => $valNombre['mensaje']]); return; }

            $valCedula = Validador::validarCedula($datos['cedula_solicitante'], false);
            if (!$valCedula['valido']) { echo json_encode(['success' => false, 'message' => $valCedula['mensaje']]); return; }

            $valTel1 = Validador::validarTelefono($datos['telefono1']);
            if (!$valTel1['valido']) { echo json_encode(['success' => false, 'message' => $valTel1['mensaje']]); return; }

            $valTel2 = Validador::validarTelefono($datos['telefono2'], false);
            if (!$valTel2['valido']) { echo json_encode(['success' => false, 'message' => $valTel2['mensaje']]); return; }

            $fichaId = $this->modelo->crear($datos);
            if (!$fichaId) {
                echo json_encode(['success' => false, 'message' => 'No se pudo registrar la ficha.']);
                return;
            }

            $this->modeloEvento->registrarEvento(
                (int)$_SESSION['user_id'],
                'INSERT',
                'fichas_emergencia',
                $fichaId,
                null,
                ['id' => $fichaId, 'caso' => $datos['caso_id'], 'estado' => 'Pendiente'],
                "Ficha de emergencia #{$fichaId} creada."
            );

            echo json_encode(['success' => true, 'message' => "Ficha #{$fichaId} registrada correctamente.", 'id' => $fichaId]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    //--------------------------------------------------------------------
    // POST Actualiza los datos de una ficha existente
    //--------------------------------------------------------------------
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
            
            // Regla de blindaje táctico: Cierre inmutable
            if (in_array($anterior['estado_ficha'], ['Cerrado', 'Finalizado'])) {
                echo json_encode(['success' => false, 'message' => 'No se permite editar una emergencia que ya se encuentra Cerrada o Finalizada.']);
                return;
            }

            $valParroquia = Validador::validarId($datos['parroquia_id'], 'Parroquia');
            if (!$valParroquia['valido']) { echo json_encode(['success' => false, 'message' => $valParroquia['mensaje']]); return; }

            $valCaso = Validador::validarId($datos['caso_id'], 'Caso');
            if (!$valCaso['valido']) { echo json_encode(['success' => false, 'message' => $valCaso['mensaje']]); return; }

            $valDireccion = Validador::validarTextoLibre($datos['direccion_exacta'], 'Dirección Exacta', 10, 500);
            if (!$valDireccion['valido']) { echo json_encode(['success' => false, 'message' => $valDireccion['mensaje']]); return; }

            $valDesc = Validador::validarTextoLibre($datos['descripcion_caso'], 'Descripción del Caso', 10, 1000);
            if (!$valDesc['valido']) { echo json_encode(['success' => false, 'message' => $valDesc['mensaje']]); return; }

            $valNombre = Validador::validarNombreCompleto($datos['nombre_solicitante']);
            if (!$valNombre['valido']) { echo json_encode(['success' => false, 'message' => $valNombre['mensaje']]); return; }

            $valCedula = Validador::validarCedula($datos['cedula_solicitante'], false);
            if (!$valCedula['valido']) { echo json_encode(['success' => false, 'message' => $valCedula['mensaje']]); return; }

            $valTel1 = Validador::validarTelefono($datos['telefono1']);
            if (!$valTel1['valido']) { echo json_encode(['success' => false, 'message' => $valTel1['mensaje']]); return; }

            $valTel2 = Validador::validarTelefono($datos['telefono2'], false);
            if (!$valTel2['valido']) { echo json_encode(['success' => false, 'message' => $valTel2['mensaje']]); return; }

            $exito = $this->modelo->actualizar($fichaId, $datos, (int)$_SESSION['user_id']);

            if ($exito) {
                $this->modeloEvento->registrarEvento(
                    (int)$_SESSION['user_id'], 'UPDATE', 'fichas_emergencia', $fichaId,
                    $anterior, $datos, "Ficha #{$fichaId} actualizada."
                );
                echo json_encode(['success' => true, 'message' => "Ficha #{$fichaId} actualizada."]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la ficha.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    //--------------------------------------------------------------------
    // POST Cambia el estado de una ficha
    //--------------------------------------------------------------------
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

            $fichaId     = (int)($_POST['ficha_id']     ?? 0);
            $nuevoEstado = trim($_POST['nuevo_estado']  ?? '');

            if (!$fichaId || $nuevoEstado === '') {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
                return;
            }

            $anterior = $this->modelo->obtenerPorId($fichaId);
            if (!$anterior) {
                echo json_encode(['success' => false, 'message' => 'Ficha no encontrada.']);
                return;
            }
            
            // Regla de blindaje táctico: Cierre inmutable para estados terminales
            if (in_array($anterior['estado_ficha'], ['Cerrado', 'Finalizado'])) {
                echo json_encode(['success' => false, 'message' => 'No se permite reabrir ni modificar el estado de una ficha que ya fue Cerrada o Finalizada.']);
                return;
            }

            $exito = $this->modelo->cambiarEstado($fichaId, $nuevoEstado, (int)$_SESSION['user_id']);

            if ($exito) {
                $this->modeloEvento->registrarEvento(
                    (int)$_SESSION['user_id'], 'CAMBIO_ESTADO', 'fichas_emergencia', $fichaId,
                    ['estado' => $anterior['estado_ficha']],
                    ['estado' => $nuevoEstado],
                    "Ficha #{$fichaId} cambió de '{$anterior['estado_ficha']}' a '{$nuevoEstado}'."
                );
                echo json_encode(['success' => true, 'message' => "Estado actualizado a '{$nuevoEstado}'.", 'nuevo_estado' => $nuevoEstado]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    //--------------------------------------------------------------------
    // GET Retorna los datos de una ficha en JSON (para el modal de detalle)
    //--------------------------------------------------------------------
    public function detalle(): void {
        header('Content-Type: application/json');
        try {
            $id   = (int)($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false]); return; }
            $ficha = $this->modelo->obtenerPorId($id);
            echo json_encode(['success' => (bool)$ficha, 'data' => $ficha]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ================================================================
    // AJAX — Configuración (solo Admin)
    // ================================================================

    public function obtenerParroquiasPorMunicipio(): void {
        header('Content-Type: application/json');
        $municipioId = (int)($_GET['municipio_id'] ?? 0);
        echo json_encode($this->modelo->obtenerParroquias($municipioId ?: null));
    }

    public function obtenerCasosPorTipo(): void {
        header('Content-Type: application/json');
        $tipoId = (int)($_GET['tipo_id'] ?? 0);
        echo json_encode($this->modelo->obtenerCasos($tipoId ?: null));
    }

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
                        
                        return ($accion === 'crear') 
                            ? $this->modelo->crearCaso($tipoId, $nombre, $desc) 
                            : $this->modelo->actualizarCaso($id, $tipoId, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoCaso($id),
                    default    => false,
                },
                'municipio' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $nombre = trim($_POST['nombre_municipio'] ?? $_POST['nombre'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        // Validación estricta para Municipios: max 30 caracteres, solo letras.
                        $v = Validador::validarNombreAlfabetico($nombre, 'Municipio', 30);
                        if (!$v['valido']) {
                            return ['valido' => false, 'mensaje' => $v['mensaje']];
                        }
                        // Validación de texto libre para la descripción
                        $vDesc = Validador::validarTextoLibre($desc, 'Descripción', 0, 256);
                        if (!$vDesc['valido']) {
                            return ['valido' => false, 'mensaje' => $vDesc['mensaje']];
                        }

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
                        
                        return ($accion === 'crear') 
                            ? $this->modelo->crearParroquia($munId, $nombre, $desc) 
                            : $this->modelo->actualizarParroquia($id, $munId, $nombre, $desc);
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
                            if (!$vDesc['valido']) {
                                return ['valido' => false, 'mensaje' => $vDesc['mensaje']];
                            }
                        }

                        return ($accion === 'crear') ? $this->modelo->crearOrganismo($nombre, $desc) : $this->modelo->actualizarOrganismo($id, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoOrganismo($id),
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

    //--------------------------------------------------------------------
    // GET Retorna los registros de configuración en JSON para DataTables
    //--------------------------------------------------------------------
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

            $datos = match ($catalogo) {
                'tipo_emergencia' => $this->modelo->obtenerTiposEmergencia($estado),
                'caso'            => $this->modelo->obtenerCasos($tipoId ?: null, $estado),
                'municipio'       => $this->modelo->obtenerMunicipios($estado),
                'parroquia'       => $this->modelo->obtenerParroquias($municipioId ?: null, $estado),
                'organismo'       => $this->modelo->obtenerOrganismos($estado),
                default           => [],
            };

            echo json_encode(['data' => $datos]);
        } catch (\Exception $e) {
            error_log("[FichaControlador] Error en obtenerCatalogo: " . $e->getMessage());
            echo json_encode(['data' => []]);
        }
    }
}
