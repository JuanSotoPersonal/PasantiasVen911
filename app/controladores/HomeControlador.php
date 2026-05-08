<?php
/**
 * CONTROLADOR: HomeControlador
 * Propósito: Gestionar el aterrizaje de los usuarios tras el inicio de sesión.
 * Consolida las estadísticas del sistema para el Dashboard principal.
 */

require_once 'app/modelos/UsuarioModelo.php';
use App\modelos\UsuarioModelo;

class HomeControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. SEGURIDAD Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida que el usuario tenga una sesión activa antes de permitir el acceso al inicio.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. RENDERIZADO (DASHBOARD)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa y muestra la vista de inicio, consolidando contadores de gestión básicos.
     */
    public function index() {
        try {
            require_once 'app/modelos/HomeModelo.php';
            $homeModelo = new \App\modelos\HomeModelo();
            
            $rolId = (int)($_SESSION['user_rol_id'] ?? 0);
            $userId = (int)($_SESSION['user_id'] ?? 0);

            $stats = [];

            // 2.1 Carga de estadísticas segmentadas por rol
            $municipios = [];
            $tiposEmergencia = [];

            switch ($rolId) {
                case 1: // Administrador
                    $stats = $homeModelo->obtenerResumenAdmin();
                    break;
                case 2: // Operador
                    $stats = $homeModelo->obtenerResumenOperador($userId);
                    
                    // Necesario para el modal de creación rápida
                    require_once 'app/modelos/FichaModelo.php';
                    $fichaMod = new \App\modelos\FichaModelo();
                    $municipios = $fichaMod->obtenerMunicipios();
                    $tiposEmergencia = $fichaMod->obtenerTiposEmergencia();
                    break;
                case 3: // Despachador
                    $stats = $homeModelo->obtenerResumenDespachador($userId);
                    break;
                case 4: // Jefatura
                    $stats = $homeModelo->obtenerResumenJefatura();
                    break;
            }

            // Pasamos los datos a la vista
            $datos = $stats;

            // 2.2 Carga del contenedor principal de la vista home 
            require_once 'app/vista/home/index.php';
        } catch (\Exception $e) {
            error_log("[HomeControlador] Error en index: " . $e->getMessage());
            die("Ocurrió un error inesperado al cargar el inicio.");
        }
    }

    /**
     * Endpoint AJAX para obtener estadísticas en tiempo real sin recargar la página.
     */
    public function obtenerStatsAjax() {
        header('Content-Type: application/json');
        try {
            require_once 'app/modelos/HomeModelo.php';
            $homeModelo = new \App\modelos\HomeModelo();
            
            $rolId = (int)($_SESSION['user_rol_id'] ?? 0);
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $stats = [];

            switch ($rolId) {
                case 1: $stats = $homeModelo->obtenerResumenAdmin(); break;
                case 2: $stats = $homeModelo->obtenerResumenOperador($userId); break;
                case 3: $stats = $homeModelo->obtenerResumenDespachador($userId); break;
                case 4: $stats = $homeModelo->obtenerResumenJefatura(); break;
            }

            echo json_encode(['success' => true, 'datos' => $stats]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
