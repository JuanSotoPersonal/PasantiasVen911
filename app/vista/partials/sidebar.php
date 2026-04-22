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
    <div class="sidebar-scroll-area">
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
        <?php $tabFicha = $_GET['t'] ?? 'todas'; ?>
        <li class="nav-item <?= $seccion === 'ficha' ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= $seccion === 'ficha' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-file-earmark-medical-fill"></i>
            <p>Fichas<i class="nav-arrow bi bi-chevron-right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="index.php?url=ficha&t=todas" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'todas') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'ficha' && $tabFicha === 'todas') ? 'bi-collection-fill' : 'bi-collection' ?>"></i>
                <p>Todas las Fichas</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=ficha&t=pendientes" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'pendientes') ? 'active' : '' ?>">
                <i class="nav-icon bi bi-hourglass-split"></i>
                <p>Pendientes</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=ficha&t=en_proceso" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'en_proceso') ? 'active' : '' ?>">
                <i class="nav-icon bi bi-arrow-repeat"></i>
                <p>En Proceso</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=ficha&t=cerradas" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'cerradas') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'ficha' && $tabFicha === 'cerradas') ? 'bi-lock-fill' : 'bi-lock' ?>"></i>
                <p>Cerradas</p>
              </a>
            </li>
            <?php if (tienePerm('configuracion', 'gestionar')): ?>
            <li class="nav-item">
              <a href="index.php?url=ficha&t=configuracion" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'configuracion') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'ficha' && $tabFicha === 'configuracion') ? 'bi-gear-fill' : 'bi-gear' ?>"></i>
                <p>Configuración</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
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
        <li class="nav-item <?= $seccion === 'usuario' ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= $seccion === 'usuario' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>Usuarios<i class="nav-arrow bi bi-chevron-right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php $tab = $_GET['t'] ?? 'todos'; ?>
            <li class="nav-item">
              <a href="index.php?url=usuario&t=todos" class="nav-link <?= ($seccion === 'usuario' && $tab === 'todos') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'usuario' && $tab === 'todos') ? 'bi-people-fill' : 'bi-people' ?>"></i>
                <p>Todos los Usuarios</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=usuario&t=rol_1" class="nav-link <?= ($seccion === 'usuario' && $tab === 'rol_1') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'usuario' && $tab === 'rol_1') ? 'bi-shield-fill' : 'bi-shield' ?>"></i>
                <p>Administradores</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=usuario&t=rol_2" class="nav-link <?= ($seccion === 'usuario' && $tab === 'rol_2') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'usuario' && $tab === 'rol_2') ? 'bi-headset' : 'bi-headset' ?>"></i>
                <p>Operadores</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=usuario&t=rol_3" class="nav-link <?= ($seccion === 'usuario' && $tab === 'rol_3') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'usuario' && $tab === 'rol_3') ? 'bi-broadcast' : 'bi-broadcast' ?>"></i>
                <p>Despachadores</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=usuario&t=rol_4" class="nav-link <?= ($seccion === 'usuario' && $tab === 'rol_4') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'usuario' && $tab === 'rol_4') ? 'bi-star-fill' : 'bi-star' ?>"></i>
                <p>Jefatura</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=usuario&t=inactivos" class="nav-link <?= ($seccion === 'usuario' && $tab === 'inactivos') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'usuario' && $tab === 'inactivos') ? 'bi-person-x-fill' : 'bi-person-x' ?>"></i>
                <p>Usuarios Inactivos</p>
              </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>

        <?php if (tienePerm('historial', 'ver')): ?>
        <!--Módulo Historial de Logs-->
        <li class="nav-item <?= $seccion === 'evento' ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= $seccion === 'evento' ? 'active' : '' ?>">
            <i class="nav-icon bi bi-clock-history"></i>
            <p>Historial<i class="nav-arrow bi bi-chevron-right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php $tabHistorial = $_GET['t'] ?? 'sistema'; ?>
            <li class="nav-item">
              <a href="index.php?url=evento&t=sistema" class="nav-link <?= ($seccion === 'evento' && $tabHistorial === 'sistema') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'evento' && $tabHistorial === 'sistema') ? 'bi-display-fill' : 'bi-display' ?>"></i>
                <p>Auditoría Sistema</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?url=evento&t=ficha" class="nav-link <?= ($seccion === 'evento' && $tabHistorial === 'ficha') ? 'active' : '' ?>">
                <i class="nav-icon bi <?= ($seccion === 'evento' && $tabHistorial === 'ficha') ? 'bi-file-earmark-medical-fill' : 'bi-file-earmark-medical' ?>"></i>
                <p>Historial Fichas</p>
              </a>
            </li>
          </ul>
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
    </div><!-- fin::sidebar-scroll-area -->

    <!--inicio::Bloque fijo al fondo-->
    <div class="sidebar-sticky-footer">

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

      <!--inicio::Logos Institucionales-->
      <div class="sidebar-institutional-logo">
        <img src="public/assets/img/logos/LOGO MIJP JUSTICIA Y PAZ - BLANCO (1).webp"
             alt="MIJP"
             class="sidebar-mijp-logo">
        <div class="footer-logo-divider"></div>
        <img src="public/assets/img/logos/VEN 9-1-1.webp"
             alt="VEN 9-1-1"
             class="sidebar-footer-ven-logo">
      </div>
      <!--fin::Logos Institucionales-->

    </div>
    <!--fin::Bloque fijo al fondo-->

  </div>
  <!--fin::Envoltorio de la Barra Lateral-->
</aside>
<!--fin::Barra Lateral-->
