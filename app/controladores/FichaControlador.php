<?php
/**
 * CONTROLADOR: FichaControlador
 * Propósito: Gestionar el flujo operativo de las Fichas de Emergencia.
 * Maneja la recepción de incidentes, asignación de estados y administración de catálogos.
 */

require_once 'app/modelos/FichaModelo.php';
require_once 'app/modelos/DespachoModelo.php';
require_once 'app/Servicios/FichaServicio.php';
require_once 'app/Helpers/Validador.php';

use App\modelos\FichaModelo;
use App\modelos\DespachoModelo;
use App\Servicios\FichaServicio;
use App\Helpers\Validador;

class FichaControlador {

    private FichaModelo    $modelo;
    private DespachoModelo $modeloDespacho;
    private FichaServicio  $servicio;

    /**
     * Valida la sesión activa e instancia el servicio y modelos necesarios.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        $this->modelo          = new FichaModelo();
        $this->modeloDespacho  = new DespachoModelo();
        $this->servicio        = new FichaServicio();
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
        session_write_close(); // Liberar bloqueo de sesión para permitir concurrencia en AJAX
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
                'comuna_id'          => (int)($_POST['comuna_id'] ?? 0),
                'sector_id'          => (int)($_POST['sector_id'] ?? 0),
                'direccion_exacta'   => trim($_POST['direccion_exacta'] ?? ''),
                'caso_id'            => (int)($_POST['caso_id'] ?? 0),
                'descripcion_caso'   => trim($_POST['descripcion_caso'] ?? ''),
                'nombre_solicitante' => trim($_POST['nombre_solicitante'] ?? ''),
                'cedula_solicitante' => trim($_POST['cedula_solicitante'] ?? ''),
                'telefono1'          => trim($_POST['telefono1'] ?? ''),
                'telefono2'          => trim($_POST['telefono2'] ?? ''),
            ];

            $respuesta = $this->servicio->crearFicha($datos, (int)$_SESSION['user_id'], $_SESSION['user_name']);
            echo json_encode($respuesta);

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
                'comuna_id'          => (int)($_POST['comuna_id'] ?? 0),
                'sector_id'          => (int)($_POST['sector_id'] ?? 0),
                'direccion_exacta'   => trim($_POST['direccion_exacta'] ?? ''),
                'caso_id'            => (int)($_POST['caso_id'] ?? 0),
                'descripcion_caso'   => trim($_POST['descripcion_caso'] ?? ''),
                'nombre_solicitante' => trim($_POST['nombre_solicitante'] ?? ''),
                'cedula_solicitante' => trim($_POST['cedula_solicitante'] ?? ''),
                'telefono1'          => trim($_POST['telefono1'] ?? ''),
                'telefono2'          => trim($_POST['telefono2'] ?? ''),
            ];

            $usuarioNombre = $_SESSION['user_name'] ?? 'Usuario';
            $respuesta = $this->servicio->actualizarFicha($fichaId, $datos, (int)$_SESSION['user_id'], $usuarioNombre);
            echo json_encode($respuesta);

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

            $respuesta = $this->servicio->cambiarEstado($fichaId, $nuevoEstado, (int)$_SESSION['user_id'], $_SESSION['user_name'], $motivoCierre);
            echo json_encode($respuesta);

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
            if (!$ficha) { echo json_encode(['success' => false]); return; }

            $dataResponse = ['success' => true, 'data' => $ficha];

            // 5.1 PROTECCIÓN RBAC: Solo Administrador, Despachador y Jefatura ven organismos
            if ((int)$_SESSION['user_rol_id'] !== 2) {
                $dataResponse['despachos'] = $this->modeloDespacho->obtenerDespachosDeFicha($id);
            }

            echo json_encode($dataResponse);
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
                        $desc   = trim($_POST['descripcion'] ?? '');
                        $v = Validador::validarNombreCatalogo($nombre, 'Nombre del Tipo');
                        if (!$v['valido']) return $v;
                        if (!empty($desc)) {
                            $vDesc = Validador::validarTextoLibre($desc, 'Descripción', 0, 255);
                            if (!$vDesc['valido']) return $vDesc;
                        }
                        return ($accion === 'crear') ? $this->modelo->crearTipoEmergencia($nombre, $desc) : $this->modelo->actualizarTipoEmergencia($id, $nombre, $desc);
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

                        if (!empty($desc)) {
                            $vDesc = Validador::validarTextoLibre($desc, 'Descripción', 0, 255);
                            if (!$vDesc['valido']) return $vDesc;
                        }

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
                'comuna' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $parId  = (int)($_POST['parroquia_id'] ?? 0);
                        $nombre = trim($_POST['nombre_comuna'] ?? $_POST['nombre'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        $vId = Validador::validarId($parId, 'Parroquia');
                        if (!$vId['valido']) return $vId;
                        $vNom = Validador::validarNombreCatalogo($nombre, 'Nombre de la Comuna');
                        if (!$vNom['valido']) return $vNom;
                        return ($accion === 'crear') ? $this->modelo->crearComuna($parId, $nombre, $desc) : $this->modelo->actualizarComuna($id, $parId, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoComuna($id),
                    default    => false,
                },
                'sector' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $comId  = (int)($_POST['comuna_id'] ?? 0);
                        $nombre = trim($_POST['nombre_sector'] ?? $_POST['nombre'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        $vId = Validador::validarId($comId, 'Comuna');
                        if (!$vId['valido']) return $vId;
                        $vNom = Validador::validarNombreCatalogo($nombre, 'Nombre del Sector');
                        if (!$vNom['valido']) return $vNom;
                        return ($accion === 'crear') ? $this->modelo->crearSector($comId, $nombre, $desc) : $this->modelo->actualizarSector($id, $comId, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoSector($id),
                    default    => false,
                },
                'cuadrante' => match ($accion) {
                    'crear', 'editar' => (function() use ($id, $accion) {
                        $secId  = (int)($_POST['sector_id'] ?? 0);
                        $orgId  = (int)($_POST['organismo_id'] ?? 0);
                        $nombre = trim($_POST['nombre_cuadrante'] ?? $_POST['nombre'] ?? '');
                        $desc   = trim($_POST['descripcion'] ?? '');
                        
                        $vId = Validador::validarId($secId, 'Sector');
                        if (!$vId['valido']) return $vId;
                        
                        $vNom = Validador::validarNombreCatalogo($nombre, 'Nombre del Cuadrante');
                        if (!$vNom['valido']) return $vNom;
                        
                        return ($accion === 'crear') 
                            ? $this->modelo->crearCuadrantePaz($secId, $orgId > 0 ? $orgId : null, $nombre, $desc) 
                            : $this->modelo->actualizarCuadrantePaz($id, $secId, $orgId > 0 ? $orgId : null, $nombre, $desc);
                    })(),
                    'eliminar' => $this->modelo->toggleEstadoCuadrantePaz($id),
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
                            : $this->modelo->actualizarMotivoCierre($id, $nombre, $desc, $contexto);
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
        session_write_close(); // Liberar bloqueo de sesión
        try {
            if (!tienePerm('configuracion', 'gestionar') && !tienePerm('fichas', 'ver') && !tienePerm('despacho', 'ver')) {
                echo json_encode(['data' => []]);
                return;
            }
            $catalogo    = $_GET['cat']          ?? '';
            $tipoId      = (int)($_GET['tipo_id']      ?? 0);
            $municipioId = (int)($_GET['municipio_id'] ?? 0);
            $parroquiaId = (int)($_GET['parroquia_id'] ?? 0);
            $comunaId    = (int)($_GET['comuna_id'] ?? 0);
            $sectorId    = (int)($_GET['sector_id'] ?? 0);
            $organismoId = (int)($_GET['organismo_id'] ?? 0);
            $estadoGeog  = (int)($_GET['estado_id'] ?? 0);
            $estado      = (int)($_GET['estado'] ?? 1);
            // Contexto diferenciador para motivos de cierre: 'ficha' o 'organismo'
            $contexto    = in_array($_GET['contexto'] ?? '', ['ficha', 'organismo']) ? $_GET['contexto'] : 'ficha';

            $datos = match ($catalogo) {
                'tipo_emergencia' => $this->modelo->obtenerTiposEmergencia($estado),
                'caso'            => $this->modelo->obtenerCasos($tipoId ?: null, $estado),
                'municipio'       => $this->modelo->obtenerMunicipios($estado),
                'parroquia'       => $this->modelo->obtenerParroquias($municipioId ?: null, $estado),
                'comuna'          => $this->modelo->obtenerComunas($parroquiaId ?: null, $estado),
                'sector'          => $this->modelo->obtenerSectores($comunaId ?: null, $estado),
                'cuadrante'       => $this->modelo->obtenerCuadrantesPaz($sectorId ?: null, $estado, $organismoId ?: null),
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
