<?php
/**
 * Partial: Navbar (Barra de Navegación Superior)
 * Incluido en: home.php (y cualquier otra vista que lo necesite)
 */
?>
<!--inicio::Cabecera-->
<nav class="app-header navbar navbar-expand bg-white shadow-sm border-bottom-success">
  <!--inicio::Contenedor-->
  <div class="container-fluid">
    <!--inicio::Enlaces de Navegación de Inicio-->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list"></i>
        </a>
      </li>
      <li class="nav-item d-none d-md-block">
        <a href="#" class="nav-link">Inicio</a>
      </li>
    </ul>
    <!--fin::Enlaces de Navegación de Inicio-->

    <!--inicio::Enlaces de Navegación de Fin-->
    <ul class="navbar-nav ms-auto">
      <?php require __DIR__ . '/profile_bubble.php'; ?>
    </ul>
    <!--fin::Enlaces de Navegación de Fin-->
  </div>
  <!--fin::Contenedor-->
</nav>
<!--fin::Cabecera-->
