<?php
/**
 * navbar.php - Barra de Navegación Superior
 * 
 * Gestiona el acceso rápido a módulos core, el trigger del sidebar
 * y los contenedores de notificaciones y perfil de usuario.
 */
?>

<nav class="app-header navbar navbar-expand bg-white shadow-sm border-bottom-success">
    <div class="container-fluid">
        
        <!-- 1. BLOQUE IZQUIERDO: CONTROLES DE NAVEGACIÓN -->
        <ul class="navbar-nav align-items-center">
            <!-- Toggle Sidebar (Mobile/Desktop) -->
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list fs-5"></i>
                </a>
            </li>
            
            <!-- Enlaces Directos (Módulos frecuentes) -->
            <li class="nav-item d-none d-md-block ms-2">
                <a href="index.php?url=home" class="nav-link">
                    <i class="bi bi-house-door-fill me-1 text-success"></i>Inicio
                </a>
            </li>

            <?php if (tienePerm('reportes', 'ver')): ?>
                <li class="nav-item d-none d-md-block">
                    <a href="index.php?url=reporte" class="nav-link">
                        <i class="bi bi-file-earmark-bar-graph-fill me-1 text-success"></i>Reportes
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (tienePerm('fichas', 'ver')): ?>
                <li class="nav-item d-none d-md-block">
                    <a href="index.php?url=ficha" class="nav-link">
                        <i class="bi bi-file-earmark-text-fill me-1 text-success"></i>Fichas
                    </a>
                </li>
            <?php endif; ?>

            <?php if (tienePerm('despachos', 'ver')): ?>
                <li class="nav-item d-none d-md-block">
                    <a href="index.php?url=despacho" class="nav-link">
                        <i class="bi bi-broadcast me-1 text-success"></i>Despacho
                    </a>
                </li>
            <?php endif; ?>

            <?php if (tienePerm('fichas', 'ver')): ?>
                <li class="nav-item d-none d-md-block">
                    <a href="index.php?url=notificacion" class="nav-link">
                        <i class="bi bi-bell-fill me-1 text-success"></i>Notificaciones
                    </a>
                </li>
            <?php endif; ?>

            <?php if (tienePerm('usuarios', 'ver')): ?>
                <li class="nav-item d-none d-md-block">
                    <a href="index.php?url=usuario" class="nav-link">
                        <i class="bi bi-people-fill me-1 text-success"></i>Usuarios
                    </a>
                </li>
            <?php endif; ?>

            <?php if (tienePerm('historial', 'ver')): ?>
                <li class="nav-item d-none d-md-block">
                    <a href="index.php?url=evento" class="nav-link">
                        <i class="bi bi-shield-check me-1 text-success"></i>Auditoría
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- 2. BLOQUE DERECHO: UTILIDADES Y PERFIL -->
        <ul class="navbar-nav ms-auto align-items-center">
            <!-- Badge de Rol (Elegante) -->
            <?php 
                $rolNom  = $_SESSION['user_rol'] ?? '';
            ?>
            <?php if ($rolNom): ?>
                <li class="nav-item d-none d-sm-block me-2">
                    <span class="badge px-3 py-2 rounded-pill fw-bold shadow-sm text-white" 
                          style="font-size: 0.75rem; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;">
                        <i class="bi bi-shield-lock-fill me-1"></i><?= htmlspecialchars($rolNom) ?>
                    </span>
                </li>
            <?php endif; ?>

            <!-- Panel de Notificaciones en Tiempo Real -->
            <?php require __DIR__ . '/notificaciones_panel.php'; ?>
            
            <!-- Burbuja de Perfil y Sesión -->
            <?php require __DIR__ . '/profile_bubble.php'; ?>
        </ul>

    </div>
</nav>


