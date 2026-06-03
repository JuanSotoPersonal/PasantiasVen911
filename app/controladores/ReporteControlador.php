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

        require_once 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // 1. Crear hojas para Atendidos y No Atendidos
        $sheetAtendidos = $spreadsheet->getActiveSheet();
        $sheetAtendidos->setTitle('INCIDENTES ATENDIDOS');

        $sheetNoAtendidos = $spreadsheet->createSheet();
        $sheetNoAtendidos->setTitle('INCIDENTES NO ATENDIDOS');

        $tiposReporte = [
            ['sheet' => $sheetAtendidos, 'estado' => 'Atendido', 'nombre' => 'INCIDENTES ATENDIDOS'],
            ['sheet' => $sheetNoAtendidos, 'estado' => 'No Atendido', 'nombre' => 'INCIDENTES NO ATENDIDOS']
        ];

        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];
        $nombreMes = $meses[$mes] ?? 'MES';
        $numDias = (int)date('t', strtotime("{$anio}-{$mes}-01"));

        // Obtener todos los casos activos para construir las filas ordenadas
        $todosCasos = $this->fichaModelo->obtenerCasos(null, 1);

        foreach ($tiposReporte as $reporte) {
            $sheet = $reporte['sheet'];
            $estado = $reporte['estado'];
            $nombreTabla = $reporte['nombre'];

            // Mostrar cuadrícula (Gridlines)
            $sheet->setShowGridlines(true);

            // Ajustar anchos de columnas para estética
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(40);
            
            // Columnas de días
            $smallCols = [
                'C','D','E','F','G','H','I','K','L','M','N','O','P','Q',
                'S','T','U','V','W','X','Y','Z','AB','AC','AD','AE','AF',
                'AG','AH','AI','AJ'
            ];
            foreach ($smallCols as $col) {
                $sheet->getColumnDimension($col)->setWidth(5);
            }
            
            // Columnas de totales y porcentaje
            $mediumCols = ['J','R','AA','AK','AL','AM'];
            foreach ($mediumCols as $col) {
                $sheet->getColumnDimension($col)->setWidth(12);
            }

            // --- DISEÑO DE CABECERAS ---

            // Fila de Título Principal (Rows 1 a 7)
            $sheet->mergeCells('A1:AM7');
            $sheet->setCellValue('A1', "CENTROS DE COMANDO, CONTROL Y TELECOMUNICACIONES VEN 9-1-1");
            
            $styleTitle = [
                'font' => [
                    'name' => 'Arial Black',
                    'size' => 20,
                    'bold' => true,
                    'color' => ['rgb' => '000000']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ];
            $sheet->getStyle('A1:AM7')->applyFromArray($styleTitle);

            // Fila de Sede, Mes y Año (Row 8)
            $sheet->mergeCells('C8:R8');
            $sheet->setCellValue('C8', 'CENTRO DE COMANDO VEN 9-1-1');

            $sheet->mergeCells('S8:AA8');
            $sheet->setCellValue('S8', $nombreMes);

            $sheet->mergeCells('AB8:AJ8');
            $sheet->setCellValue('AB8', $anio);

            $styleSedeMesAnio = [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 12,
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '000080']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle('C8:AJ8')->applyFromArray($styleSedeMesAnio);

            // Fila 9 y 10: Semanas, Días y Demografía
            $sheet->mergeCells('A9:A10');
            $sheet->setCellValue('A9', 'N°');

            $sheet->mergeCells('B9:B10');
            $sheet->setCellValue('B9', $nombreTabla);

            $sheet->mergeCells('C9:I9');
            $sheet->setCellValue('C9', 'SEMANA 1');

            $sheet->mergeCells('J9:J10');
            $sheet->setCellValue('J9', 'TOTAL SEMANA 1');

            $sheet->mergeCells('K9:Q9');
            $sheet->setCellValue('K9', 'SEMANA 2');

            $sheet->mergeCells('R9:R10');
            $sheet->setCellValue('R9', 'TOTAL SEMANA 2');

            $sheet->mergeCells('S9:Z9');
            $sheet->setCellValue('S9', 'SEMANA 3');

            $sheet->mergeCells('AA9:AA10');
            $sheet->setCellValue('AA9', 'TOTAL SEMANA 3');

            $sheet->mergeCells('AB9:AJ9');
            $sheet->setCellValue('AB9', 'SEMANA 4');

            $sheet->mergeCells('AK9:AK10');
            $sheet->setCellValue('AK9', 'TOTAL SEMANA 4');

            $sheet->mergeCells('AL9:AL10');
            $sheet->setCellValue('AL9', 'TOTAL');

            $sheet->mergeCells('AM9:AM10');
            $sheet->setCellValue('AM9', '%');

            // Valores Fila 10 (Días individuales y Demografía)
            // Semana 1: 1 a 7
            for ($d = 1; $d <= 7; $d++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $d);
                $sheet->setCellValue("{$colLetter}10", $d);
            }
            // Semana 2: 8 a 14
            for ($d = 8; $d <= 14; $d++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $d);
                $sheet->setCellValue("{$colLetter}10", $d);
            }
            // Semana 3: 15 a 22
            for ($d = 15; $d <= 22; $d++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 + $d);
                $sheet->setCellValue("{$colLetter}10", $d);
            }
            // Semana 4: 23 a 31
            for ($d = 23; $d <= 31; $d++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5 + $d);
                $sheet->setCellValue("{$colLetter}10", $d);
            }


            // Estilos Fila 9 y 10
            $styleHeaders = [
                'font' => [
                    'name' => 'Calibri',
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '003366']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ];
            $sheet->getStyle('A9:AM10')->applyFromArray($styleHeaders);

            // Ajustar tamaños de letras en las cabeceras
            $sheet->getStyle('A9:B10')->getFont()->setSize(10);
            $sheet->getStyle('C9:I9')->getFont()->setSize(10);
            $sheet->getStyle('J9:J10')->getFont()->setSize(9);
            $sheet->getStyle('K9:Q9')->getFont()->setSize(10);
            $sheet->getStyle('R9:R10')->getFont()->setSize(9);
            $sheet->getStyle('S9:Z9')->getFont()->setSize(10);
            $sheet->getStyle('AA9:AA10')->getFont()->setSize(9);
            $sheet->getStyle('AB9:AJ9')->getFont()->setSize(10);
            $sheet->getStyle('AK9:AK10')->getFont()->setSize(9);
            $sheet->getStyle('AL9:AL10')->getFont()->setSize(10);
            $sheet->getStyle('AM9:AM10')->getFont()->setSize(10);
            $sheet->getStyle('C10:AM10')->getFont()->setSize(8);

            // --- VOLCADO DE DATOS ---

            // Obtener de la BD los conteos agrupados para este mes y estado
            $datosQuery = $this->reporteModelo->obtenerMatrizAcumuladaMensual($mes, $anio, $estado);
            $conteos = [];
            foreach ($datosQuery as $row) {
                $casoId = (int)$row['caso_id'];
                $dia = (int)$row['dia'];
                $totalDia = (int)$row['total_dia'];
                $conteos[$casoId][$dia] = $totalDia;
            }

            $currentRow = 11;
            $contador = 1;

            foreach ($todosCasos as $caso) {
                $casoId = (int)$caso['id'];
                $nombreCaso = $caso['nombre_caso'];

                $sheet->setCellValue("A{$currentRow}", $contador++);
                $sheet->setCellValue("B{$currentRow}", $nombreCaso);

                // Semana 1 (1 a 7) -> Columnas C (3) a I (9)
                for ($d = 1; $d <= 7; $d++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $d);
                    $val = (isset($conteos[$casoId][$d]) && $d <= $numDias) ? $conteos[$casoId][$d] : "";
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                }
                $sheet->setCellValue("J{$currentRow}", "=SUM(C{$currentRow}:I{$currentRow})");

                // Semana 2 (8 a 14) -> Columnas K (11) a Q (17)
                for ($d = 8; $d <= 14; $d++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $d);
                    $val = (isset($conteos[$casoId][$d]) && $d <= $numDias) ? $conteos[$casoId][$d] : "";
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                }
                $sheet->setCellValue("R{$currentRow}", "=SUM(K{$currentRow}:Q{$currentRow})");

                // Semana 3 (15 a 22) -> Columnas S (19) a Z (26)
                for ($d = 15; $d <= 22; $d++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 + $d);
                    $val = (isset($conteos[$casoId][$d]) && $d <= $numDias) ? $conteos[$casoId][$d] : "";
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                }
                $sheet->setCellValue("AA{$currentRow}", "=SUM(S{$currentRow}:Z{$currentRow})");

                // Semana 4 (23 a 31) -> Columnas AB (28) a AJ (36)
                for ($d = 23; $d <= 31; $d++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5 + $d);
                    $val = (isset($conteos[$casoId][$d]) && $d <= $numDias) ? $conteos[$casoId][$d] : "";
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                }
                $sheet->setCellValue("AK{$currentRow}", "=SUM(AB{$currentRow}:AJ{$currentRow})");

                // Total Fila
                $sheet->setCellValue("AL{$currentRow}", "=SUM(J{$currentRow},R{$currentRow},AA{$currentRow},AK{$currentRow})");

                $currentRow++;
            }

            $lastCaseRow = $currentRow - 1;
            $totalRowIndex = $currentRow;

            // Fórmulas de Porcentaje y Formato
            for ($row = 11; $row <= $lastCaseRow; $row++) {
                $sheet->setCellValue("AM{$row}", "=IF(AL{$totalRowIndex}>0, AL{$row}/AL{$totalRowIndex}, 0)");
                $sheet->getStyle("AM{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            }

            // --- FILA DE TOTAL GENERAL AL FINAL ---
            $sheet->setCellValue("B{$totalRowIndex}", "TOTAL");

            // Sumatoria de cada columna de C a AM
            for ($colIdx = 3; $colIdx <= 39; $colIdx++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                if ($colLetter === 'AM') {
                    // Porcentaje Total
                    $sheet->setCellValue("AM{$totalRowIndex}", "=SUM(AM11:AM{$lastCaseRow})");
                    $sheet->getStyle("AM{$totalRowIndex}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                } else {
                    $sheet->setCellValue("{$colLetter}{$totalRowIndex}", "=SUM({$colLetter}11:{$colLetter}{$lastCaseRow})");
                }
            }

            // --- ESTILIZACIÓN DE CELDAS DE DATOS Y BORDES ---

            // Alineación de datos
            $sheet->getStyle("A11:A{$lastCaseRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B11:B{$lastCaseRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C11:AM{$lastCaseRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A11:AM{$lastCaseRow}")->getFont()->setSize(10);

            // Estilos fila total
            $styleTotalRow = [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 10,
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '808080']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle("A{$totalRowIndex}:AM{$totalRowIndex}")->applyFromArray($styleTotalRow);
            $sheet->getStyle("B{$totalRowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            // Aplicar bordes finos a todo el bloque de datos y cabeceras
            $styleBorders = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'BFBFBF']
                    ]
                ]
            ];
            $sheet->getStyle("A8:AM{$totalRowIndex}")->applyFromArray($styleBorders);
        }

        // 3. Forzar descarga del archivo Excel XLSX
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="acumulado_incidencias_' . strtolower($nombreMes) . '_' . $anio . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
