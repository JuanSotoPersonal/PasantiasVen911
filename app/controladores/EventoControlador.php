<?php

require_once 'app/modelos/EventoModelo.php';
use App\modelos\EventoModelo;

class EventoControlador {

    private EventoModelo $modelo;

    public function __construct() {
        // Validacion: requiere permiso 'ver' en módulo 'historial' (via RBAC en sesión)
        if (!isset($_SESSION['user_id']) || !tienePerm('historial', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->modelo = new EventoModelo();
    }

    //--------------------------------------------------------------------
    // Muestra la vista principal del historial
    //--------------------------------------------------------------------
    public function index(): void {
        require_once 'app/vista/eventos/index.php';
    }

    //--------------------------------------------------------------------
    // Retorna los registros en formato JSON compatible con DataTables
    // en modo serverSide. Lee parámetros POST enviados por DataTables.
    //--------------------------------------------------------------------
    public function obtenerDatos(): void {
        header('Content-Type: application/json');
        try {
            // Parámetros estándar de DataTables server-side
            $draw     = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
            $inicio   = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
            $cantidad = isset($_POST['length']) ? (int)$_POST['length'] : 10;
            $busqueda = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

            // Columna y dirección de ordenamiento
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
}
