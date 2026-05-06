<?php
/**
 * Componente: Gráfica de Usuarios
 * Muestra la distribución de usuarios activos e inactivos.
 */
?>
<div class="card h-100 shadow-sm border-0 rounded-4">
    <div class="card-header bg-white border-bottom p-3">
        <h3 class="card-title text-success fw-bold mb-0">
            <i class="bi bi-pie-chart-fill me-2 text-warning"></i>Distribución de Personal por Rol
        </h3>
    </div>
    <div class="card-body p-4 d-flex align-items-center justify-content-center">
        <!-- Contenedor donde se dibuja la gráfica vía JS -->
        <div id="usuariosChart" style="width: 100%; min-height: 350px;"></div>
    </div>
</div>
