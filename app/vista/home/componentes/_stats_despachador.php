<?php
/**
 * Componente: Dashboard Despachador
 */
?>
<div class="row g-4">
    <!-- Pendientes -->
    <div class="col-md-6 col-12">
        <div class="card shadow-sm border-0 rounded-4 bg-warning text-dark h-100">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">Fichas Pendientes</h4>
                    <p class="mb-0">Esperando despacho</p>
                </div>
                <p class="display-4 fw-bold mb-0" id="counter_pendientes_globales"><?php echo $datos['pendientes_globales'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <!-- Activos -->
    <div class="col-md-6 col-12">
        <div class="card shadow-sm border-0 rounded-4 bg-info text-white h-100">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">Mis Despachos Activos</h4>
                    <p class="mb-0">En camino / En sitio</p>
                </div>
                <p class="display-4 fw-bold mb-0" id="counter_mis_despachos_activos"><?php echo $datos['mis_despachos_activos'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <!-- Top Organismos -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-primary fw-bold mb-0">
                    <i class="bi bi-building me-2"></i>Organismos más Solicitados
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="organismosChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
