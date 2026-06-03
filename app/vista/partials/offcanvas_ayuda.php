<?php
/**
 * offcanvas_ayuda.php - Panel lateral de Ayuda Rápida (Manual de Usuario Estático)
 * 
 * Muestra información contextual dependiendo del módulo donde se encuentre el usuario.
 */
?>
<div class="offcanvas offcanvas-end offcanvas-help" tabindex="-1" id="offcanvasAyuda" aria-labelledby="offcanvasAyudaLabel" data-bs-scroll="true" data-bs-backdrop="false">
  <div class="offcanvas-header offcanvas-help-header">
    <h5 class="offcanvas-title fw-bold" id="offcanvasAyudaLabel">
        <i class="bi bi-book-half me-2"></i>Manual Rápido
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    
    <!-- Encabezado de Contexto con Rol y Módulo -->
    <div class="help-header-box d-flex align-items-center justify-content-between p-2 mb-3 bg-light rounded border">
        <span class="small fw-semibold text-secondary" id="ayuda-active-module">Sección: Cargando...</span>
        <span class="badge" id="ayuda-rol-badge">Cargando...</span>
    </div>

    <!-- Contexto Dinámico -->
    <div class="help-context-box shadow-sm mb-4">
        <h6 class="fw-bold text-success mb-3 d-flex align-items-center" id="ayuda-contexto-titulo">
            <i class="bi bi-info-circle-fill me-2"></i>Ayuda de esta sección
        </h6>
        <div class="text-secondary small" id="ayuda-contexto-cuerpo">
            <!-- Cargado por JS -->
            Cargando información contextual...
        </div>
    </div>

    <!-- Tips Generales (Fijos) -->
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Tips Generales</h6>
    <div class="list-group help-quick-links mb-4">
        <div class="list-group-item">
            <strong class="d-block text-dark small mb-1">Navegación</strong>
            <span class="text-secondary" style="font-size: 0.8rem;">Use el menú lateral para moverse entre los diferentes módulos. Si un módulo tiene flecha, contiene submódulos.</span>
        </div>
        <div class="list-group-item">
            <strong class="d-block text-dark small mb-1">Perfil y Configuración</strong>
            <span class="text-secondary" style="font-size: 0.8rem;">Haga clic en su nombre en la esquina superior derecha para ver su perfil o cerrar sesión.</span>
        </div>
        <div class="list-group-item">
            <strong class="d-block text-dark small mb-1">Notificaciones</strong>
            <span class="text-secondary" style="font-size: 0.8rem;">Revise la campana superior para ver alertas sobre fichas nuevas o despachos.</span>
        </div>
    </div>

    <!-- Enlace a FAQ completo -->
    <div class="d-grid mt-auto pt-3 border-top">
        <a href="index.php?url=ayuda" class="btn btn-outline-success fw-bold">
            <i class="bi bi-book me-2"></i>Ver el Manual de Usuario completo
        </a>
    </div>

  </div>
</div>
