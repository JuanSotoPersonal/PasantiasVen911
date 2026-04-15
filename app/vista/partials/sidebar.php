<?php
    //--------------------------------------------------------------------
    // Partial: Sidebar (Barra Lateral)
    // Incluido en: home.php y cualquier otra vista autenticada.
    // Usa la función tienePerm() definida en index.php para mostrar
    // solo los módulos a los que el usuario tiene permiso de 'ver'.
    //--------------------------------------------------------------------
    $urlActual = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
    $seccion   = explode('/', $urlActual)[0] ?? '';
?>
<!--inicio::Barra Lateral-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--inicio::Marca de la Barra Lateral-->
  <div class="sidebar-brand">
    <a href="index.php?url=home" class="brand-link">
      <span class="brand-text">
        <span class="brand-text-main">Ven 911</span>
        <span class="brand-text-sub">Carabobo</span>
      </span>
    </a>
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
        <!--Dashboard: siempre visible-->
        <li class="nav-item">
          <a href="index.php?url=home" class="nav-link <?= $seccion === 'home' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Inicio</p>
          </a>
        </li>

        <?php if (tienePerm('fichas', 'ver')): ?>
        <!--Módulo Fichas de Emergencia-->
        <li class="nav-item">
          <a href="index.php?url=ficha" class="nav-link <?= $seccion === 'ficha' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-file-earmark-text-fill"></i>
            <p>Fichas</p>
          </a>
        </li>
        <?php endif; ?>

        <?php if (tienePerm('despachos', 'ver')): ?>
        <!--Módulo Despachos-->
        <li class="nav-item">
          <a href="index.php?url=despacho" class="nav-link <?= $seccion === 'despacho' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-broadcast"></i>
            <p>Despachos</p>
          </a>
        </li>
        <?php endif; ?>

        <?php if (tienePerm('usuarios', 'ver')): ?>
        <!--Módulo Usuarios-->
        <li class="nav-item">
          <a href="index.php?url=usuario" class="nav-link <?= $seccion === 'usuario' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>Usuarios</p>
          </a>
        </li>
        <?php endif; ?>

        <?php if (tienePerm('historial', 'ver')): ?>
        <!--Módulo Historial de Logs-->
        <li class="nav-item">
          <a href="index.php?url=evento" class="nav-link <?= $seccion === 'evento' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-clock-history"></i>
            <p>Historial</p>
          </a>
        </li>
        <?php endif; ?>

        <?php if (tienePerm('reportes', 'ver')): ?>
        <!--Módulo Reportes-->
        <li class="nav-item">
          <a href="index.php?url=reporte" class="nav-link <?= $seccion === 'reporte' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-bar-chart-fill"></i>
            <p>Reportes</p>
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
