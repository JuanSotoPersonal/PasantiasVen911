<?php

/**
 * ReporteControlador - Gestión del módulo de Reportes e Inteligencia
 */
class ReporteControlador {

    private $reporteModelo;
    private $fichaModelo;

    public function __construct() {
        // Verificar sesión y rol (Solo Jefatura=4 y Admin=1)
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol_id'], [1, 4])) {
            header('Location: index.php?url=auth');
            exit;
        }

        require_once 'app/modelos/ReporteModelo.php';
        require_once 'app/modelos/FichaModelo.php';
        
        $this->reporteModelo = new \App\modelos\ReporteModelo();
        $this->fichaModelo = new \App\modelos\FichaModelo();
    }

    /**
     * Vista principal de reportes
     */
    public function index() {
        // Cargar catálogos para los filtros
        $datos = [
            'titulo' => 'Reportes e Inteligencia Operativa',
            'municipios' => $this->fichaModelo->obtenerMunicipios(),
            'tipos_emergencia' => $this->fichaModelo->obtenerTiposEmergencia(),
            'operadores' => $this->obtenerOperadores(),
            'js' => ['reportes/reportes.js']
        ];

        require_once 'app/vista/reportes/index.php';
    }

    /**
     * Procesar búsqueda filtrada vía AJAX
     */
    public function buscar() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $filtros = [
            'desde' => $_POST['desde'] ?? '',
            'hasta' => $_POST['hasta'] ?? '',
            'municipio_id' => $_POST['municipio_id'] ?? '',
            'tipo_emergencia_id' => $_POST['tipo_emergencia_id'] ?? '',
            'usuario_id' => $_POST['usuario_id'] ?? '',
            'estado' => $_POST['estado'] ?? ''
        ];

        $resultados = $this->reporteModelo->obtenerFichasFiltradas($filtros);
        $resumen = $this->reporteModelo->obtenerResumenFiltrado($filtros);

        echo json_encode([
            'success' => true,
            'data' => $resultados,
            'resumen' => $resumen
        ]);
    }

    /**
     * Auxiliar: Obtener lista de usuarios con rol Operador
     */
    private function obtenerOperadores() {
        $sql = "SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 ORDER BY nombre_completo ASC";
        $stmt = $this->reporteModelo->getConexion()->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
