<?php
/**
 * Componente: Dashboard Operador
 */
?>
<div class="row g-4">
    <!-- Botón Gigante Crear Ficha -->
    <div class="col-lg-4 col-md-6 col-12">
        <div class="card shadow-sm border-0 rounded-4 card-btn-crear text-white h-100 cursor-pointer hover-scale" id="btnNuevaFicha">
            <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center text-center">
                <i class="bi bi-plus-circle-fill btn-crear-icon"></i>
                <h3 class="fw-bold mb-0">CREAR FICHA</h3>
                <p class="mb-0 fw-medium">Registrar nueva emergencia</p>
            </div>
        </div>
    </div>

    <!-- Resumen Hoy -->
    <div class="col-lg-4 col-md-6 col-12">
        <div class="card shadow-sm border-0 rounded-4 bg-white border-start border-success border-5 h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center text-center">
                <i class="bi bi-file-earmark-plus-fill display-4 mb-3 text-success"></i>
                <h4 class="fw-bold mb-0 text-dark">Fichas Creadas Hoy</h4>
                <p class="display-3 fw-bold mb-0 text-success" id="counter_total_hoy"><?php echo $datos['total_hoy'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <!-- Mis Estados -->
    <div class="col-lg-4 col-12">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-primary fw-bold mb-0">
                    <i class="bi bi-pie-chart-fill me-2"></i>Mis Fichas por Estado
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="misEstadosChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>

    <!-- Actividad Semanal -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-success fw-bold mb-0">
                    <i class="bi bi-graph-up me-2"></i>Mi Actividad (Últimos 7 días)
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="actividadSemanalChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
