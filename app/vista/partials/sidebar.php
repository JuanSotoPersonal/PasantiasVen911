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
      <!--inicio::Texto de Marca-->
      <span class="brand-text">
        <span class="brand-text-main">Ven 911</span>
        <span class="brand-text-sub">Carabobo</span>
      </span>
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
            <p>Inicio</p>
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
        <!--Historial de Logs (Solo Super Admin)-->
        <li class="nav-item">
          <a href="index.php?url=log" class="nav-link <?= $seccion === 'log' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-clock-history"></i>
            <p>Historial</p>
          </a>
        </li>
        <?php endif; ?>

      </ul>
      <!--fin::Menú de la Barra Lateral-->

    </nav>
    <!--inicio::Pie de Sidebar-->
    <div class="sidebar-footer-nav">
      <ul class="nav sidebar-menu flex-column">
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-question-circle"></i>
            <p>Preguntas Frecuentes</p>
          </a>
        </li>
      </ul>
    </div>
    <!--fin::Pie de Sidebar-->
  </div>
  <!--fin::Envoltorio de la Barra Lateral-->
</aside>
<!--fin::Barra Lateral-->

