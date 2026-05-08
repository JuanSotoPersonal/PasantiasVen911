<?php
/**
 * Componente: Dashboard Administrador
 */
?>
<div class="row g-4">
    <!-- Distribución de Personal -->
    <div class="col-xl-6 col-lg-6 col-12">
        <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-primary fw-bold mb-0">
                    <i class="bi bi-people-fill me-2"></i>Personal por Rol
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="rolesChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Top Emergencias -->
    <div class="col-xl-6 col-lg-6 col-12">
        <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-danger fw-bold mb-0">
                    <i class="bi bi-fire me-2"></i>Tipos de Emergencia Frecuentes
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="emergenciasChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Estado Global de Fichas -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <h3 class="card-title text-success fw-bold mb-0">
                    <i class="bi bi-activity me-2"></i>Estado Global de Fichas
                </h3>
            </div>
            <div class="card-body p-4">
                <div id="estadosGlobalChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
