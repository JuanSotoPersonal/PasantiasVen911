<?php
/**
 * index.php - Vista de Reportes e Inteligencia Operativa
 */
$pageName = 'reporte';
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Reportes e Inteligencia</title>
    <?php require __DIR__ . '/../partials/head.php'; ?>
    <link rel="stylesheet" href="public/css/reportes.css">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main py-4 bg-light">
            <div class="container-fluid px-4">
                <!-- 1. ENCABEZADO Y ACCIONES RÁPIDAS -->
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold text-dark mb-1">
                                <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Reportes e Inteligencia
                            </h2>
                            <p class="text-muted mb-0">Generación de informes y análisis de datos operativos del VEN 911</p>
                        </div>
                        <div class="btn-group shadow-sm">
                            <button type="button" class="btn btn-outline-danger" id="btnExportarPDF">
                                <i class="bi bi-file-pdf-fill me-1"></i> Exportar PDF
                            </button>
                            <button type="button" class="btn btn-outline-success" id="btnExportarExcel">
                                <i class="bi bi-file-excel-fill me-1"></i> Exportar Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. RESUMEN KPI DINÁMICO -->
                <div id="contenedorResumenKPI">
                    <?php include 'componentes/_resumen_kpi.php'; ?>
                </div>

                <div class="row">
                    <!-- 3. PANEL DE FILTROS (IZQUIERDA) -->
                    <div class="col-lg-3 col-md-4">
                        <?php include 'componentes/_filtros.php'; ?>
                    </div>

                    <!-- 4. TABLA DE RESULTADOS (DERECHA) -->
                    <div class="col-lg-9 col-md-8">
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold mb-0">Vista Previa de Resultados</h5>
                                <span class="badge bg-primary rounded-pill" id="totalResultadosBadge">0 registros</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="tablaReportes">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha/Hora</th>
                                                <th>Código</th>
                                                <th>Municipio</th>
                                                <th>Emergencia</th>
                                                <th>Operador</th>
                                                <th>Estado</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyReportes">
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-search display-4 d-block mb-2"></i>
                                                    Utilice los filtros para iniciar la búsqueda
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php require __DIR__ . '/../partials/footer.php'; ?>
    </div>
    <?php require __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>
