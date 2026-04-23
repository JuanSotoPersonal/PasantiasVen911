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
        <ul class="navbar-nav">
            <!-- Toggle Sidebar (Mobile/Desktop) -->
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            
            <!-- Enlaces Directos (Módulos frecuentes) -->
            <li class="nav-item d-none d-md-block">
                <a href="index.php?url=home" class="nav-link">
                    <i class="bi bi-house-door-fill me-1 text-success"></i>Inicio
                </a>
            </li>

            <?php if (tienePerm('fichas', 'ver')): ?>
                <li class="nav-item d-none d-md-block">
                    <a href="index.php?url=ficha" class="nav-link">
                        <i class="bi bi-file-earmark-text-fill me-1 text-success"></i>Fichas
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
        </ul>

        <!-- 2. BLOQUE DERECHO: UTILIDADES Y PERFIL -->
        <ul class="navbar-nav ms-auto">
            <!-- Panel de Notificaciones en Tiempo Real -->
            <?php require __DIR__ . '/notificaciones_panel.php'; ?>
            
            <!-- Burbuja de Perfil y Sesión -->
            <?php require __DIR__ . '/profile_bubble.php'; ?>
        </ul>

    </div>
</nav>

