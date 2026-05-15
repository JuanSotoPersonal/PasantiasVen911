<?php
/**
 * notificaciones_panel.php - Componente Dropdown de Notificaciones
 * 
 * Gestiona la visualización de alertas en tiempo real (SSE) y el historial
 * rápido de eventos del sistema para el usuario autenticado.
 */
?>

<li class="nav-item dropdown notificaciones-dropdown" id="notificaciones-nav-item">
    
    <!-- 1. DISPARADOR (BELL ICON) -->
    <a class="nav-link position-relative" href="#" id="notificaciones-btn"
       data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
        <i class="bi bi-bell-fill fs-5"></i>
        <span class="badge-notif-count d-none" id="notif-badge">0</span>
    </a>

    <!-- 2. PANEL DESPLEGABLE -->
    <div class="dropdown-menu dropdown-menu-end notif-dropdown-panel shadow-lg p-0"
         aria-labelledby="notificaciones-btn">
         
        <!-- Cabecera del panel -->
        <div class="notif-panel-header d-flex justify-content-between align-items-center px-3 py-2">
            <span class="fw-bold text-white">
                <i class="bi bi-bell me-1"></i> Notificaciones
            </span>
            <button class="btn btn-sm btn-outline-light btn-marcar-todas" id="btn-marcar-todas"
                    title="Marcar todas como leídas">
                <i class="bi bi-check2-all"></i>
            </button>
        </div>

        <!-- Lista de notificaciones (Poblada vía JS/SSE) -->
        <ul class="notif-lista list-unstyled mb-0" id="notif-lista">
            <li class="notif-item-vacio text-center text-muted py-4" id="notif-vacio">
                <i class="bi bi-bell-slash fs-3 d-block mb-1"></i>
                Sin notificaciones nuevas
            </li>
        </ul>

        <!-- Pie: Enlace al historial completo -->
        <div class="notif-panel-footer text-center border-top py-2">
            <a href="index.php?url=notificacion" class="text-success text-decoration-none small fw-semibold">
                <i class="bi bi-list-ul me-1"></i>Ver todas
            </a>
        </div>
        
    </div>
</li>

