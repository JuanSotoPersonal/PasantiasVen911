<?php

namespace App\Servicios;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * ReporteServicio - Encapsula la lógica de generación y diseño estético de reportes en Excel XLSX.
 */
class ReporteServicio {

    /**
     * Genera el Excel XLSX para el Reporte Operativo Detallado.
     * Incluye tarjetas KPI coloreadas arriba y zebra-striping verde en los datos.
     * 
     * @param array $fichas
     * @param array $resumen
     * @param string $desde
     * @param string $hasta
     * @return Spreadsheet
     */
    public function generarReporteOperativoExcel(array $fichas, array $resumen, string $desde = '', string $hasta = ''): Spreadsheet {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('REPORTE OPERATIVO');
        
        // Mostrar cuadrícula (Gridlines)
        $sheet->setShowGridlines(true);

        // Ajustar anchos de columnas
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(15);

        // 1. TÍTULO PRINCIPAL (Filas 1 a 3)
        $sheet->mergeCells('A1:G3');
        $dateText = "";
        if (!empty($desde) && !empty($hasta)) {
            $dateText = "del " . date('d-m-Y', strtotime($desde)) . " hasta el " . date('d-m-Y', strtotime($hasta));
        } else {
            $dateText = date('d-m-Y');
        }
        $sheet->setCellValue('A1', "SISTEMA INTEGRADO DE GESTIÓN DE EMERGENCIAS VEN 9-1-1\nReporte Operativo " . $dateText);
        
        $styleTitle = [
            'font' => [
                'name' => 'Calibri',
                'size' => 14,
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '064E3B'] // Verde profundo del sistema
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A1:G3')->applyFromArray($styleTitle);

        // 2. SECCIÓN KPIs (Filas 5 a 6)
        // Dibujamos tarjetas para los KPIs
        $kpis = [
            ['label' => 'TOTAL FICHAS', 'valor' => $resumen['total'],      'bg' => '064E3B'], // Verde profundo
            ['label' => 'PENDIENTES',   'valor' => $resumen['pendientes'], 'bg' => 'D27D2D'], // Naranja
            ['label' => 'EN PROCESO',   'valor' => $resumen['en_proceso'],  'bg' => '17A2B8'], // Cyan
            ['label' => 'ATENDIDAS',    'valor' => $resumen['atendidas'],  'bg' => '16A34A'], // Verde primario
            ['label' => 'CANCELADAS',   'valor' => $resumen['canceladas'],  'bg' => 'DC3545'], // Rojo
            ['label' => 'EFECTIVIDAD',  'valor' => $resumen['efectividad'] . '%', 'bg' => '15803D'] // Verde oscuro
        ];

        // Columnas Excel para KPIs: B a G (6 columnas)
        $cols = ['B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($kpis as $idx => $kpi) {
            $col = $cols[$idx];
            
            // Fila 5: Label
            $sheet->setCellValue("{$col}5", $kpi['label']);
            // Fila 6: Valor
            $sheet->setCellValue("{$col}6", $kpi['valor']);
            
            // Estilos para tarjeta de KPI
            $styleKPI = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $kpi['bg']]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'FFFFFF']
                    ]
                ]
            ];
            $sheet->getStyle("{$col}5:{$col}6")->applyFromArray($styleKPI);
            $sheet->getStyle("{$col}5")->getFont()->setSize(9);
            $sheet->getStyle("{$col}6")->getFont()->setSize(12);
        }

        // 3. CABECERA DE TABLA (Fila 8)
        $headers = ['N°', 'Fecha', 'Municipio', 'Emergencia', 'Tipo de Caso', 'Operador', 'Estado'];
        foreach ($headers as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}8", $header);
        }

        $styleTableHeaders = [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '16A34A'] // Verde principal
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ];
        $sheet->getStyle('A8:G8')->applyFromArray($styleTableHeaders);

        // 4. DATOS (Fila 9 en adelante)
        $currentRow = 9;
        $contador = 1;

        foreach ($fichas as $f) {
            $sheet->setCellValue("A{$currentRow}", $contador++);
            $sheet->setCellValue("B{$currentRow}", date('d/m/Y H:i', strtotime($f['fecha_creacion'])));
            $sheet->setCellValue("C{$currentRow}", $f['nombre_municipio']);
            $sheet->setCellValue("D{$currentRow}", $f['nombre_emergencia']);
            $sheet->setCellValue("E{$currentRow}", $f['nombre_caso'] ?? '—');
            $sheet->setCellValue("F{$currentRow}", $f['nombre_operador']);
            $sheet->setCellValue("G{$currentRow}", $f['estado_ficha']);

            // Zebra striping para filas pares
            if ($currentRow % 2 === 0) {
                $styleZebra = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0FDF4'] // Verde light
                    ]
                ];
                $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($styleZebra);
            }

            // Alineación de datos
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currentRow++;
        }

        $lastRow = $currentRow - 1;

        // Aplicar bordes finos a todo el bloque de la tabla
        $styleBorders = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BFBFBF']
                ]
            ]
        ];
        $sheet->getStyle("A8:G{$lastRow}")->applyFromArray($styleBorders);

        return $spreadsheet;
    }

    /**
     * Genera el Excel XLSX para el Reporte Acumulado Mensual de Incidencias.
     * 
     * @param int $mes
     * @param int $anio
     * @param string $nombreMes
     * @param int $numDias
     * @param array $todosCasos
     * @param array $conteosAtendidos
     * @param array $conteosNoAtendidos
     * @return Spreadsheet
     */
    public function generarAcumuladoMensualExcel(
        int $mes, 
        int $anio, 
        string $nombreMes, 
        int $numDias, 
        array $todosCasos, 
        array $conteosAtendidos, 
        array $conteosNoAtendidos,
        array $despachosAtendidos = [],
        array $despachosNoAtendidos = []
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet();

        // 1. Crear hojas Atendidos, Organismos Atendidos, No Atendidos y Organismos No Atendidos
        $sheetAtendidos = $spreadsheet->getActiveSheet();
        $sheetAtendidos->setTitle('INCIDENTES ATENDIDOS');

        $sheetOrgAtendidos = $spreadsheet->createSheet();
        $sheetOrgAtendidos->setTitle('ORGANISMOS ATENDIDOS');

        $sheetNoAtendidos = $spreadsheet->createSheet();
        $sheetNoAtendidos->setTitle('INCIDENTES NO ATENDIDOS');

        $sheetOrgNoAtendidos = $spreadsheet->createSheet();
        $sheetOrgNoAtendidos->setTitle('ORG ARTICULADOS NO ATENDIDO');

        $tiposReporte = [
            ['sheet' => $sheetAtendidos, 'conteos' => $conteosAtendidos, 'nombre' => 'INCIDENTES ATENDIDOS'],
            ['sheet' => $sheetNoAtendidos, 'conteos' => $conteosNoAtendidos, 'nombre' => 'INCIDENTES NO ATENDIDOS']
        ];

        foreach ($tiposReporte as $reporte) {
            $sheet = $reporte['sheet'];
            $conteos = $reporte['conteos'];
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
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
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
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '064E3B'] // Verde profundo
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle('C8:AJ8')->applyFromArray($styleSedeMesAnio);

            // Fila 9 y 10: Semanas, Días y Totales
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

            // Valores Fila 10 (Días individuales)
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
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16A34A'] // Verde principal
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
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
            $currentRow = 11;
            $contador = 1;

            foreach ($todosCasos as $caso) {
                $casoId = (int)$caso['id'];
                $nombreCaso = $caso['nombre_caso'];

                $sheet->setCellValue("A{$currentRow}", $contador++);
                $sheet->setCellValue("B{$currentRow}", $nombreCaso);

                // Semana 1 (1 a 7)
                for ($d = 1; $d <= 7; $d++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $d);
                    $val = (isset($conteos[$casoId][$d]) && $d <= $numDias) ? $conteos[$casoId][$d] : "";
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                }
                $sheet->setCellValue("J{$currentRow}", "=SUM(C{$currentRow}:I{$currentRow})");

                // Semana 2 (8 a 14)
                for ($d = 8; $d <= 14; $d++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $d);
                    $val = (isset($conteos[$casoId][$d]) && $d <= $numDias) ? $conteos[$casoId][$d] : "";
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                }
                $sheet->setCellValue("R{$currentRow}", "=SUM(K{$currentRow}:Q{$currentRow})");

                // Semana 3 (15 a 22)
                for ($d = 15; $d <= 22; $d++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 + $d);
                    $val = (isset($conteos[$casoId][$d]) && $d <= $numDias) ? $conteos[$casoId][$d] : "";
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $val);
                }
                $sheet->setCellValue("AA{$currentRow}", "=SUM(S{$currentRow}:Z{$currentRow})");

                // Semana 4 (23 a 31)
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
                $sheet->getStyle("AM{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            }

            // --- FILA DE TOTAL GENERAL AL FINAL ---
            $sheet->setCellValue("B{$totalRowIndex}", "TOTAL");

            // Sumatoria de cada columna de C a AM
            for ($colIdx = 3; $colIdx <= 39; $colIdx++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                if ($colLetter === 'AM') {
                    $sheet->setCellValue("AM{$totalRowIndex}", "=SUM(AM11:AM{$lastCaseRow})");
                    $sheet->getStyle("AM{$totalRowIndex}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
                } else {
                    $sheet->setCellValue("{$colLetter}{$totalRowIndex}", "=SUM({$colLetter}11:{$colLetter}{$lastCaseRow})");
                }
            }

            // --- ESTILIZACIÓN DE CELDAS DE DATOS E ALINEACIÓN ---

            $sheet->getStyle("A11:A{$lastCaseRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B11:B{$lastCaseRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C11:AM{$lastCaseRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '14532D'] // Verde oscuro
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle("A{$totalRowIndex}:AM{$totalRowIndex}")->applyFromArray($styleTotalRow);
            $sheet->getStyle("B{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Aplicar bordes finos a todo el bloque
            $styleBorders = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'BFBFBF']
                    ]
                ]
            ];
            $sheet->getStyle("A8:AM{$totalRowIndex}")->applyFromArray($styleBorders);
        }

        // --- RENDERIZADO DE HOJAS DE ORGANISMOS ---
        $orgSheets = [
            [
                'sheet' => $sheetOrgAtendidos, 
                'datos' => $despachosAtendidos, 
                'titulo_col' => 'ORGANISMOS ARTICULADOS ATENDIDOS',
                'es_atendido' => true
            ],
            [
                'sheet' => $sheetOrgNoAtendidos, 
                'datos' => $despachosNoAtendidos, 
                'titulo_col' => 'ORGANISMOS ARTICULADOS NO ATENDIDOS',
                'es_atendido' => false
            ]
        ];

        foreach ($orgSheets as $cfg) {
            $sheet = $cfg['sheet'];
            $datos = $cfg['datos'];
            $tituloCol = $cfg['titulo_col'];
            $esAtendido = $cfg['es_atendido'];

            $sheet->setShowGridlines(true);

            // Ajustar anchos
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(35);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(35);
            if (!$esAtendido) {
                $sheet->getColumnDimension('I')->setWidth(45);
            }

            // 1. Banner de Título (Filas 1 a 7)
            $maxColLetter = !$esAtendido ? 'I' : 'H';
            $sheet->mergeCells("A1:{$maxColLetter}7");
            $sheet->setCellValue('A1', "CENTROS DE COMANDO, CONTROL Y TELECOMUNICACIONES VEN 9-1-1");
            
            $styleTitle = [
                'font' => [
                    'name' => 'Arial Black',
                    'size' => 16,
                    'bold' => true,
                    'color' => ['rgb' => '000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ];
            $sheet->getStyle("A1:{$maxColLetter}7")->applyFromArray($styleTitle);

            // 2. Cabecera (Fila 8)
            $headers = ['N°', 'FECHA', $tituloCol, 'N° DE CUADRANTE DE PAZ', 'ESTADO', 'MUNICIPIO', 'PARROQUIA', 'TIPO DE INCIDENCIA'];
            if (!$esAtendido) {
                $headers[] = 'MOTIVO DE NO ATENCIÓN';
            }

            foreach ($headers as $colIdx => $header) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue("{$colLetter}8", $header);
            }

            $styleHeaders = [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 10,
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16A34A'] // Verde principal
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ];
            $sheet->getStyle("A8:{$maxColLetter}8")->applyFromArray($styleHeaders);

            // 3. Volcado de Datos (Fila 9 en adelante)
            $currentRow = 9;
            $contador = 1;
            foreach ($datos as $d) {
                $sheet->setCellValue("A{$currentRow}", $contador++);
                $sheet->setCellValue("B{$currentRow}", date('d-m-Y', strtotime($d['fecha_creacion'])));
                $sheet->setCellValue("C{$currentRow}", $d['nombre_organismo']);
                $sheet->setCellValue("D{$currentRow}", $d['nombre_cuadrante'] ?? '—');
                $sheet->setCellValue("E{$currentRow}", 'CARABOBO');
                $sheet->setCellValue("F{$currentRow}", $d['nombre_municipio']);
                $sheet->setCellValue("G{$currentRow}", $d['nombre_parroquia']);
                $sheet->setCellValue("H{$currentRow}", $d['nombre_caso'] ?? '—');

                if (!$esAtendido) {
                    $motivo = '—';
                    if (!empty($d['motivo_cancelacion_despacho'])) {
                        $motivo = $d['motivo_cancelacion_despacho'];
                    } elseif (!empty($d['motivo_cierre_ficha'])) {
                        $tipo = !empty($d['tipo_motivo_cierre_ficha']) ? "[" . $d['tipo_motivo_cierre_ficha'] . "] " : "";
                        $motivo = $tipo . $d['motivo_cierre_ficha'];
                    }
                    $sheet->setCellValue("I{$currentRow}", $motivo);
                }

                // Zebra striping para filas pares
                if ($currentRow % 2 === 0) {
                    $styleZebra = [
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F0FDF4']
                        ]
                    ];
                    $sheet->getStyle("A{$currentRow}:{$maxColLetter}{$currentRow}")->applyFromArray($styleZebra);
                }

                // Alineación de celdas
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow++;
            }

            // Aplicar bordes finos a todo el bloque si hay datos
            $lastRow = $currentRow - 1;
            if ($lastRow >= 8) {
                $styleBorders = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'BFBFBF']
                        ]
                    ]
                ];
                $sheet->getStyle("A8:{$maxColLetter}{$lastRow}")->applyFromArray($styleBorders);
            }
        }

        return $spreadsheet;
    }

    /**
     * Genera el objeto FPDF para el Reporte Operativo Detallado.
     * 
     * @param array $fichas
     * @param array $resumen
     * @param string $desde
     * @param string $hasta
     * @return \FPDF
     */
    public function generarReporteOperativoPdf(array $fichas, array $resumen, string $desde = '', string $hasta = '') {
        require_once 'public/libs/fpdf/fpdf.php';

        // Definición de clase anónima para manejar cabeceras y pies de página automáticos en FPDF
        $pdf = new class('L', 'mm', 'A4') extends \FPDF {
            public $colores = [];
            public $logos = [];
            public $tablaW = [];
            public $tablaH = [];
            public $desde = '';
            public $hasta = '';

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
                
                $dateText = "";
                if (!empty($this->desde) && !empty($this->hasta)) {
                    $dateText = "del " . date('d-m-Y', strtotime($this->desde)) . " hasta el " . date('d-m-Y', strtotime($this->hasta));
                } else {
                    $dateText = date('d-m-Y');
                }

                $this->SetFont('Helvetica', 'B', 11);
                $this->Cell(0, 7, mb_convert_encoding('Reporte Operativo VEN-911 | ' . $dateText, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                
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
        $pdf->desde = $desde;
        $pdf->hasta = $hasta;
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
            ['label' => 'CERRADAS',    'valor' => $resumen['canceladas'],  'color' => [108, 117, 125]] // Nota: en base de datos es canceladas
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
        
        return $pdf;
    }
}
