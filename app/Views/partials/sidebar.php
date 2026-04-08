<?php
    //--------------------------------------------------------------------
    // Partial: Sidebar (Barra Later  al)
    // Incluido en: home.php (y cualquier otra vista que lo necesite)
    //--------------------------------------------------------------------
    // Detectar la sección activa desde la URL
    $urlActual = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
    $seccion   = explode('/', $urlActual)[0] ?? '';
?>
<!--inicio::Barra Lateral-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--inicio::Marca de la Barra Lateral-->
  <div class="sidebar-brand">
    <!--inicio::Enlace de Marca-->
    <a href="index.php?url=home" class="brand-link">
      <!--inicio::Imagen de Marca-->
      <img
        src="public/assets/img/ven911_logo.png"
        alt="Ven911 Logo"
        class="brand-image opacity-100 shadow-sm"
      />
      <!--fin::Imagen de Marca-->
      <!--inicio::Texto de Marca-->
      <span class="brand-text fw-light">Ven 911 Carabobo</span>
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
        aria-label="Navegación principal"
        data-accordion="false"
        id="navigation"
      >
        <!--Dashboard-->
        <li class="nav-item">
          <a href="index.php?url=home" class="nav-link <?= $seccion === 'home' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <?php if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == 1): ?>
        <!--Módulo de Usuarios (Solo Super Admin)-->
        <li class="nav-item">
          <a href="index.php?url=usuario" class="nav-link <?= $seccion === 'usuario' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>Usuarios</p>
          </a>
        </li>
        <?php endif; ?>

      </ul>
      <!--fin::Menú de la Barra Lateral-->
    </nav>
  </div>
  <!--fin::Envoltorio de la Barra Lateral-->
</aside>
<!--fin::Barra Lateral-->

