<?php

/**
 * ReporteControlador - Gestión del módulo de Reportes e Inteligencia
 */
class ReporteControlador {

    private $reporteModelo;
    private $fichaModelo;
    private $reporteServicio;

    public function __construct() {
        // Verificar sesión activa
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }

        // Verificar permiso del módulo (RBAC)
        if (!tienePerm('reportes', 'ver')) {
            header('Location: index.php?url=home');
            exit;
        }

        require_once 'app/modelos/ReporteModelo.php';
        require_once 'app/modelos/FichaModelo.php';
        require_once 'app/Servicios/ReporteServicio.php';
        
        $this->reporteModelo = new \App\modelos\ReporteModelo();
        $this->fichaModelo = new \App\modelos\FichaModelo();
        $this->reporteServicio = new \App\Servicios\ReporteServicio();
    }

    /**
     * Vista principal de reportes
     */
    public function index() {
        // Cargar catálogos para los filtros
        $datos = [
            'titulo'           => 'Reportes e Inteligencia Operativa',
            'municipios'       => $this->fichaModelo->obtenerMunicipios(),
            'tipos_emergencia' => $this->fichaModelo->obtenerTiposEmergencia(),
            'casos'            => $this->fichaModelo->obtenerCasos(), // Todos los casos activos
            'operadores'       => $this->reporteModelo->obtenerOperadores(),
            'js'               => ['reportes/reportes.js']
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
            'desde'              => $_POST['desde'] ?? '',
            'hasta'              => $_POST['hasta'] ?? '',
            'municipio_id'       => $_POST['municipio_id'] ?? '',
            'tipo_emergencia_id' => $_POST['tipo_emergencia_id'] ?? '',
            'caso_id'            => $_POST['caso_id'] ?? '',
            'usuario_id'         => $_POST['usuario_id'] ?? '',
            'estado'             => $_POST['estado'] ?? ''
        ];

        $resultados = $this->reporteModelo->obtenerFichasFiltradas($filtros);
        $resumen    = $this->reporteModelo->obtenerResumenFiltrado($filtros);

        echo json_encode([
            'success' => true,
            'data' => $resultados,
            'resumen' => $resumen
        ]);
    }

    /**
     * Genera el reporte de forma síncrona y lo envía directamente al navegador.
     * Esto evita las limitaciones de WebSockets y RabbitMQ para archivos binarios pesados (PDF).
     */
    public function exportarSincrono() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Método no permitido');
        }

        $filtros = [
            'desde'              => $_POST['desde'] ?? '',
            'hasta'              => $_POST['hasta'] ?? '',
            'municipio_id'       => $_POST['municipio_id'] ?? '',
            'tipo_emergencia_id' => $_POST['tipo_emergencia_id'] ?? '',
            'caso_id'            => $_POST['caso_id'] ?? '',
            'usuario_id'         => $_POST['usuario_id'] ?? '',
            'estado'             => $_POST['estado'] ?? ''
        ];

        $formato = $_POST['formato'] ?? 'pdf';
        $fichas = $this->reporteModelo->obtenerFichasFiltradas($filtros);
        $resumen = $this->reporteModelo->obtenerResumenFiltrado($filtros);

        if ($formato === 'pdf') {
            $pdf = $this->reporteServicio->generarReporteOperativoPdf($fichas, $resumen);
            if (ob_get_length()) ob_clean();
            $pdf->Output('D', 'reporte_operativo_ven911_' . date('Ymd_His') . '.pdf');
            exit;
        } else if ($formato === 'xlsx_det') {
            require_once 'vendor/autoload.php';
            $spreadsheet = $this->reporteServicio->generarReporteOperativoExcel($fichas, $resumen);
            
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="reporte_operativo_ven911_' . time() . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } else {
            die('Formato no soportado sincrónicamente.');
        }
    }

    /**
     * Genera y descarga el reporte acumulado mensual de incidencias (Excel XLSX).
     */
    public function exportarAcumuladoMensualExcel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Método no permitido');
        }

        $mes = isset($_POST['mes']) ? (int)$_POST['mes'] : (int)date('m');
        $anio = isset($_POST['anio']) ? (int)$_POST['anio'] : (int)date('Y');

        // Validar rango básico de mes y año
        if ($mes < 1 || $mes > 12 || $anio < 2000 || $anio > 2100) {
            die('Parámetros de fecha inválidos');
        }

        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];
        $nombreMes = $meses[$mes] ?? 'MES';
        $numDias = (int)date('t', strtotime("{$anio}-{$mes}-01"));

        // Obtener todos los casos activos para construir las filas ordenadas
        $todosCasos = $this->fichaModelo->obtenerCasos(null, 1);

        // Obtener de la BD los conteos agrupados para este mes y estado
        $datosAtendidos = $this->reporteModelo->obtenerMatrizAcumuladaMensual($mes, $anio, 'Atendido');
        $conteosAtendidos = [];
        foreach ($datosAtendidos as $row) {
            $conteosAtendidos[(int)$row['caso_id']][(int)$row['dia']] = (int)$row['total_dia'];
        }

        $datosNoAtendidos = $this->reporteModelo->obtenerMatrizAcumuladaMensual($mes, $anio, 'No Atendido');
        $conteosNoAtendidos = [];
        foreach ($datosNoAtendidos as $row) {
            $conteosNoAtendidos[(int)$row['caso_id']][(int)$row['dia']] = (int)$row['total_dia'];
        }

        require_once 'vendor/autoload.php';
        
        $spreadsheet = $this->reporteServicio->generarAcumuladoMensualExcel(
            $mes, 
            $anio, 
            $nombreMes, 
            $numDias, 
            $todosCasos, 
            $conteosAtendidos, 
            $conteosNoAtendidos
        );

        // Forzar descarga del archivo Excel XLSX
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="acumulado_incidencias_' . strtolower($nombreMes) . '_' . $anio . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
