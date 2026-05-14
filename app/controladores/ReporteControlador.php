<?php

/**
 * ReporteControlador - Gestión del módulo de Reportes e Inteligencia
 */
class ReporteControlador {

    private $reporteModelo;
    private $fichaModelo;

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
        
        $this->reporteModelo = new \App\modelos\ReporteModelo();
        $this->fichaModelo = new \App\modelos\FichaModelo();
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
            'operadores'       => $this->obtenerOperadores(),
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
     * Auxiliar: Obtener lista de usuarios con rol Operador
     */
    private function obtenerOperadores() {
        $sql = "SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 ORDER BY nombre_completo ASC";
        $stmt = $this->reporteModelo->getConexion()->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }



    /**
     * Genera el reporte de forma síncrona y lo envía directamente al navegador.
     * Esto evita las limitaciones de WebSockets y RabbitMQ para archivos binarios pesados (PDF).
     */
    public function exportarSincrono() {
        ob_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ob_end_clean();
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

        if ($formato === 'pdf') {
            require_once 'public/libs/fpdf/fpdf.php';
            
            // Definición de clase anónima para manejar cabeceras y pies de página automáticos
            $pdf = new class('L', 'mm', 'A4') extends \FPDF {
                public $colores = [];
                public $logos = [];
                public $tablaW = [];
                public $tablaH = [];

                function Header() {
                    // Colores del Sistema
                    $cTextoPrincipal = [44, 62, 80];
                    $cVerdeSistema   = [40, 167, 69]; // Verde profesional (Success)
                    $cNegro          = [0, 0, 0];

                    // Logos
                    if (file_exists($this->logos['mijp'])) {
                        $this->Image($this->logos['mijp'], 10, 8, 40);
                    }
                    if (file_exists($this->logos['ven911'])) {
                        $this->Image($this->logos['ven911'], 255, 8, 32);
                    }

                    $this->SetY(15);
                    $this->SetTextColor($cTextoPrincipal[0], $cTextoPrincipal[1], $cTextoPrincipal[2]);
                    $this->SetFont('Helvetica', 'B', 15);
                    $this->Cell(0, 7, mb_convert_encoding('SISTEMA INTEGRADO DE GESTIÓN DE EMERGENCIAS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                    
                    $this->SetFont('Helvetica', 'B', 11);
                    $this->Cell(0, 7, mb_convert_encoding('Reporte Operativo VEN-911 | '.date('d/m/Y'), 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                    
                    $this->Ln(12);
                    $this->SetDrawColor($cNegro[0], $cNegro[1], $cNegro[2]);
                    $this->Line(10, $this->GetY(), 287, $this->GetY());
                    $this->Ln(8);

                    // Re-dibujar cabecera de tabla
                    if (count($this->tablaH) > 0) {
                        $this->SetFont('Helvetica', 'B', 8);
                        $this->SetFillColor($cVerdeSistema[0], $cVerdeSistema[1], $cVerdeSistema[2]);
                        $this->SetDrawColor($cNegro[0], $cNegro[1], $cNegro[2]);
                        $this->SetTextColor(255, 255, 255); // Texto blanco sobre fondo verde
                        
                        for ($i = 0; $i < count($this->tablaH); $i++) {
                            $this->Cell($this->tablaW[$i], 8, mb_convert_encoding($this->tablaH[$i], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
                        }
                        $this->Ln();
                        $this->SetTextColor($cTextoPrincipal[0], $cTextoPrincipal[1], $cTextoPrincipal[2]); // Volver a texto oscuro
                        $this->SetFont('Helvetica', '', 8); 
                    }
                }

                function Footer() {
                    $this->SetY(-15);
                    $this->SetFont('Helvetica', 'I', 7);
                    $this->SetTextColor(150);
                    $this->Cell(0, 10, mb_convert_encoding('Reporte Operativo VEN 9-1-1 | Página ' . $this->PageNo(), 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
                }
            };

            // Configurar propiedades de la clase anónima
            $pdf->logos = [
                'mijp'   => 'public/assets/img/logos/LOGO MIJP JUSTICIA Y PAZ - BLANCO (1).png',
                'ven911' => 'public/assets/img/logos/VEN 9-1-1.png'
            ];
            $pdf->tablaW = [12, 35, 45, 55, 55, 45, 30];
            $pdf->tablaH = ['N°', 'Fecha', 'Municipio', 'Emergencia', 'Tipo de Caso', 'Operador', 'Estado'];

            $pdf->SetTitle(mb_convert_encoding('Reporte Operativo VEN 911', 'ISO-8859-1', 'UTF-8'));
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->AddPage();

           

            // Datos
            $pdf->SetFont('Helvetica', '', 8);
            $contador = 1;
            foreach ($fichas as $f) {
                $pdf->Cell($pdf->tablaW[0], 7, $contador++, 1, 0, 'C');
                $pdf->Cell($pdf->tablaW[1], 7, date('d/m/Y', strtotime($f['fecha_creacion'])), 1, 0, 'C');
                $pdf->Cell($pdf->tablaW[2], 7, mb_convert_encoding(substr($f['nombre_municipio'], 0, 20), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
                $pdf->Cell($pdf->tablaW[3], 7, mb_convert_encoding(substr($f['nombre_emergencia'], 0, 25), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
                $pdf->Cell($pdf->tablaW[4], 7, mb_convert_encoding(substr($f['nombre_caso'] ?? '', 0, 25), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
                $pdf->Cell($pdf->tablaW[5], 7, mb_convert_encoding(substr($f['nombre_operador'], 0, 22), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
                $pdf->Cell($pdf->tablaW[6], 7, mb_convert_encoding($f['estado_ficha'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
                $pdf->Ln();
            }

             // --- 1. RESUMEN ESTADÍSTICO (KPIs) ---
            $resumen = $this->reporteModelo->obtenerResumenFiltrado($filtros);
            
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0, 10, mb_convert_encoding('RESUMEN DE GESTIÓN OPERATIVA', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
            
            // Distribución de 5 Cajas KPI
            $xBase = 10;
            $yBase = $pdf->GetY();
            $anchoCaja = 50; 
            $altoCaja = 16;
            $gap = 6;

            $kpis = [
                ['label' => 'TOTAL FICHAS', 'valor' => $resumen['total'],      'color' => [44, 62, 80]], 
                ['label' => 'PENDIENTES',  'valor' => $resumen['pendientes'], 'color' => [255, 152, 0]],
                ['label' => 'EN PROCESO',  'valor' => $resumen['en_proceso'] ?? 0, 'color' => [23, 162, 184]],
                ['label' => 'ATENDIDAS',   'valor' => $resumen['atendidas'],  'color' => [40, 167, 69]],
                ['label' => 'CERRADAS',    'valor' => $resumen['cerradas'],   'color' => [108, 117, 125]]
            ];

            foreach ($kpis as $index => $kpi) {
                $posX = $xBase + ($index * ($anchoCaja + $gap));
                
                $pdf->SetFillColor($kpi['color'][0], $kpi['color'][1], $kpi['color'][2]);
                $pdf->Rect($posX, $yBase, $anchoCaja, $altoCaja, 'F');
                
                $pdf->SetXY($posX, $yBase + 3);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('Helvetica', 'B', 7);
                $pdf->Cell($anchoCaja, 4, mb_convert_encoding($kpi['label'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                
                $pdf->SetX($posX);
                $pdf->SetFont('Helvetica', 'B', 11);
                $pdf->Cell($anchoCaja, 7, $kpi['valor'], 0, 0, 'C');
            }

            // --- 2. BARRA DE EFECTIVIDAD (Alineada y estilizada) ---
            $pdf->SetY($yBase + $altoCaja + 6);
            $pdf->SetX($xBase);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->Cell(45, 6, mb_convert_encoding('EFECTIVIDAD OPERATIVA:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            
            $anchoBarraTotal = 210;
            $pdf->SetFillColor(236, 240, 241);
            $pdf->Rect($xBase + 48, $pdf->GetY() + 1, $anchoBarraTotal, 4, 'F');
            
            $anchoProgreso = ($anchoBarraTotal * $resumen['efectividad']) / 100;
            $pdf->SetFillColor(40, 167, 69);
            $pdf->Rect($xBase + 48, $pdf->GetY() + 1, $anchoProgreso, 4, 'F');
            
            $pdf->SetX($xBase + 48 + $anchoBarraTotal + 4);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(20, 6, $resumen['efectividad'] . '%', 0, 1, 'L');

            $pdf->Ln(6);
            $pdf->Line($xBase, $pdf->GetY(), 287, $pdf->GetY()); // Línea divisoria
            $pdf->Ln(4);
            
            $pdf->SetTextColor(44, 62, 80);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Ln(2);
            
            if (ob_get_length()) ob_clean();
            $pdf->Output('D', 'reporte_operativo_ven911_' . date('Ymd_His') . '.pdf');
            ob_end_flush();
            exit;
        } else if ($formato === 'csv') {
            if (ob_get_length()) ob_clean();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="reporte_ven911_' . time() . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // BOM para Excel UTF-8
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['#', 'Fecha', 'Municipio', 'Emergencia', 'Tipo de Caso', 'Operador', 'Estado'], ';');
            
            $contador = 1;
            foreach ($fichas as $f) {
                fputcsv($output, [
                    $contador++,
                    date('d/m/Y', strtotime($f['fecha_creacion'])),
                    $f['nombre_municipio'],
                    $f['nombre_emergencia'],
                    $f['nombre_caso'] ?? '',
                    $f['nombre_operador'],
                    $f['estado_ficha']
                ], ';');
            }
            
            fclose($output);
            exit;
        } else {
            die('Formato no soportado sincrónicamente.');
        }
    }
}
