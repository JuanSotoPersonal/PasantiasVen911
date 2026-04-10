<?php

require_once 'app/modelos/LogModelo.php';
use App\modelos\LogModelo;

class LogControlador {

    private LogModelo $modelo;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol_id'] != 1) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->modelo = new LogModelo();
    }

    //--------------------------------------------------------------------
    // Muestra la vista principal del historial
    //--------------------------------------------------------------------
    public function index(): void {
        require_once 'app/vista/logs/index.php';
    }

    //--------------------------------------------------------------------
    // Retorna todos los registros en formato JSON para DataTable
    //--------------------------------------------------------------------
    public function obtenerDatos(): void {
        header('Content-Type: application/json');
        try {
            $logs = $this->modelo->obtenerTodos();
            echo json_encode(['data' => $logs]);
        } catch (\Exception $e) {
            echo json_encode(['data' => [], 'error' => $e->getMessage()]);
        }
    }
}
