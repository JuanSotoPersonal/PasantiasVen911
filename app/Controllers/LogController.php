<?php

require_once 'app/Models/LogModel.php';
use App\Models\LogModel;

class LogController {

    private LogModel $model;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol_id'] != 1) {
            header('Location: index.php?url=home');
            exit;
        }
        $this->model = new LogModel();
    }

    //--------------------------------------------------------------------
    // Muestra la vista principal del historial
    //--------------------------------------------------------------------
    public function index(): void {
        require_once 'app/Views/logs/index.php';
    }

    //--------------------------------------------------------------------
    // Retorna todos los registros en formato JSON para DataTable
    //--------------------------------------------------------------------
    public function getData(): void {
        header('Content-Type: application/json');
        try {
            $logs = $this->model->getAll();
            echo json_encode(['data' => $logs]);
        } catch (Exception $e) {
            echo json_encode(['data' => [], 'error' => $e->getMessage()]);
        }
    }
}
