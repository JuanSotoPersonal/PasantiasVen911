<?php
/**
 * Componente: Tarjetas de Resumen KPI Dinámico
 */
?>
<div class="row g-4 mb-4">
    <div class="col-md">
        <div class="card shadow-sm border-0 rounded-4 bg-white h-100 report-card-kpi">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                    <i class="bi bi-files fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">Total Filtrado</h6>
                    <span class="h4 fw-bold mb-0" id="kpi_total">0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card shadow-sm border-0 rounded-4 bg-white h-100 border-start border-warning border-4">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">Pendientes</h6>
                    <span class="h4 fw-bold mb-0 text-warning" id="kpi_pendientes">0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card shadow-sm border-0 rounded-4 bg-white h-100 border-start border-info border-4">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="icon-shape bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                    <i class="bi bi-play-circle fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">En Proceso</h6>
                    <span class="h4 fw-bold mb-0 text-info" id="kpi_proceso">0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card shadow-sm border-0 rounded-4 bg-white h-100 border-start border-success border-4">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="icon-shape bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                    <i class="bi bi-check-circle fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">Atendidas</h6>
                    <span class="h4 fw-bold mb-0 text-success" id="kpi_atendidas">0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card shadow-sm border-0 rounded-4 bg-white h-100 border-start border-secondary border-4">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="icon-shape bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 me-3">
                    <i class="bi bi-archive fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">Cerradas</h6>
                    <span class="h4 fw-bold mb-0 text-secondary" id="kpi_cerradas">0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card shadow-sm border-0 rounded-4 bg-white h-100 border-start border-info border-4">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="icon-shape bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                    <i class="bi bi-graph-up-arrow fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">% Efectividad</h6>
                    <span class="h4 fw-bold mb-0 text-info" id="kpi_efectividad">0%</span>
                </div>
            </div>
        </div>
    </div>
</div>
