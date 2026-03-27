<?php
/**
 * Partial: Sidebar (Barra Lateral)
 * Incluido en: home.php (y cualquier otra vista que lo necesite)
 */
?>
<!--inicio::Barra Lateral-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--inicio::Marca de la Barra Lateral-->
  <div class="sidebar-brand">
    <!--inicio::Enlace de Marca-->
    <a href="./index.html" class="brand-link">
      <!--inicio::Imagen de Marca-->
      <img
        src="public/assets/img/ven911_logo.png"
        alt="Ven911 Logo"
        class="brand-image opacity-100 shadow-sm"
      />
      <!--fin::Imagen de Marca-->
      <!--inicio::Texto de Marca-->
      <span class="brand-text fw-light">Ven 911</span>
      <!--fin::Texto de Marca-->
    </a>
    <!--fin::Enlace de Marca-->
  </div>
  <!--fin::Marca de la Barra Lateral-->
  <!--inicio::Envoltorio de la Barra Lateral-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--inicio::Menú de la Barra Lateral-->
      <ul
        class="nav sidebar-menu flex-column"
        data-lte-toggle="treeview"
        role="navigation"
        aria-label="Main navigation"
        data-accordion="false"
        id="navigation"
      >
        <li class="nav-item">
          <a href="#" class="nav-link active">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Dashboard</p>
          </a>
        </li>
      </ul>
      <!--fin::Menú de la Barra Lateral-->
    </nav>
  </div>
  <!--fin::Envoltorio de la Barra Lateral-->
</aside>
<!--fin::Barra Lateral-->
