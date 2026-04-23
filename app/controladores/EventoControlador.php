<?php
/**
 * CONTROLADOR: EventoControlador
 * Propósito: Gestionar la visualización de la auditoría general del sistema.
 * Expone los registros de acciones administrativas y el historial de fichas de emergencia.
 */

require_once 'app/modelos/EventoModelo.php';
use App\modelos\EventoModelo;

class EventoControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. SEGURIDAD Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private EventoModelo $modelo;

    /**
     * Valida los permisos de auditoría (gestión de historial) antes de instanciar.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id']) || !tienePerm('historial', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->modelo = new EventoModelo();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. RENDERIZADO DE VISTAS (INDEX)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Muestra la interfaz principal del historial con soporte para pestañas.
     */
    public function index(): void {
        $tabActiva = $_GET['t'] ?? 'sistema';
        require_once 'app/vista/eventos/index.php';
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. AUDITORÍA DEL SISTEMA (DATATABLES SERVER-SIDE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna los registros de auditoría administrativa en formato JSON.
     * Implementa procesamiento del lado del servidor para optimizar el rendimiento.
     */
    public function obtenerDatos(): void {
        header('Content-Type: application/json');
        try {
            // Parámetros estándar de DataTables para paginación y ordenamiento
            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 10;
            $busqueda = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

            $colOrden = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 4;
            $dirOrden = isset($_POST['order'][0]['dir'])    ? $_POST['order'][0]['dir']          : 'desc';

            $datos           = $this->modelo->obtenerPaginado($inicio, $cantidad, $busqueda, $colOrden, $dirOrden);
            $totalRegistros  = $this->modelo->contarTodos();
            $totalFiltrados  = $busqueda !== '' ? $this->modelo->contarFiltrados($busqueda) : $totalRegistros;

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

    // ///////////////////////////////////////////////////////////////////
    // 4. HISTORIAL DE FICHAS (DATATABLES SERVER-SIDE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna los registros de auditoría específicos de las fichas de emergencia.
     */
    public function obtenerDatosFichas(): void {
        header('Content-Type: application/json');
        try {
            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 10;
            $busqueda = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

            $colOrden = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 5;
            $dirOrden = isset($_POST['order'][0]['dir'])    ? $_POST['order'][0]['dir']          : 'desc';

            $datos           = $this->modelo->obtenerPaginadoFichas($inicio, $cantidad, $busqueda, $colOrden, $dirOrden);
            $totalRegistros  = $this->modelo->contarTodosFichas();
            $totalFiltrados  = $busqueda !== '' ? $this->modelo->contarFiltradosFichas($busqueda) : $totalRegistros;

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
}
