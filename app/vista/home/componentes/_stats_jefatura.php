<?php
/**
 * Componente: Dashboard Jefatura (Estratégico)
 */
$kpis = $datos['kpis'] ?? [];
?>
<div class="row g-4">
    <!-- Indicadores de Alto Nivel -->
    <div class="col-md-4 col-12">
        <div class="card shadow-sm border-0 rounded-4 bg-white border-start border-primary border-5 h-100">
            <div class="card-body p-4 text-center">
                <i class="bi bi-activity display-5 mb-2 text-primary"></i>
                <h5 class="text-muted fw-medium mb-1">Volumen de Hoy</h5>
                <p class="display-4 fw-bold mb-0 text-dark" id="counter_total_hoy_jefatura"><?php echo $kpis['total_hoy'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-12">
        <div class="card shadow-sm border-0 rounded-4 bg-white border-start border-success border-5 h-100">
            <div class="card-body p-4 text-center">
                <i class="bi bi-check-all display-5 mb-2 text-success"></i>
                <h5 class="text-muted fw-medium mb-1">Efectividad (Atendidas)</h5>
                <p class="display-4 fw-bold mb-0 text-success" id="counter_efectividad"><?php echo $kpis['efectividad'] ?? 0; ?>%</p>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-12">
        <div class="card shadow-sm border-0 rounded-4 bg-white border-start border-danger border-5 h-100">
            <div class="card-body p-4 text-center">
                <i class="bi bi-clock-history display-5 mb-2 text-danger"></i>
                <h5 class="text-muted fw-medium mb-1">Fichas en Proceso</h5>
                <p class="display-4 fw-bold mb-0 text-danger" id="counter_pendientes_jefatura"><?php 
                    $pendientes = 0;
                    foreach(($datos['municipios'] ?? []) as $m) $pendientes += $m['pendientes'];
                    echo $pendientes;
                ?></p>
            </div>
        </div>
    </div>

    <!-- Gráfica de Comparativa Temporal (Hoy vs Ayer) -->
    <div class="col-lg-8 col-12">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-primary fw-bold mb-0">
                    <i class="bi bi-graph-up-arrow me-2"></i>Demanda de Emergencias (24h)
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="comparativaTemporalChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Gráfica de Análisis de Cierres -->
    <div class="col-lg-4 col-12">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-danger fw-bold mb-0">
                    <i class="bi bi-pie-chart-fill me-2"></i>Calidad de Cierre
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="cierresCalidadChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Eficiencia por Municipio -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-success fw-bold mb-0">
                    <i class="bi bi-geo-alt-fill me-2"></i>Carga Operativa por Municipio
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="municipiosEficienciaChart" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>
</div>
