<?php
    //--------------------------------------------------------------------
    // Partial: Navbar (Barra de Navegación Superior)
    // Incluido en: home.php (y cualquier otra vista que lo necesite)
    //--------------------------------------------------------------------
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
        <a href="index.php?url=home" class="nav-link"><i class="bi bi-house-door-fill me-1"></i>Inicio</a>
      </li>

      <?php if (tienePerm('fichas', 'ver')): ?>
        <li class="nav-item d-none d-md-block">
          <a href="index.php?url=ficha" class="nav-link">
            <i class="bi bi-file-earmark-text-fill me-1"></i>Fichas
          </a>
        </li>
      <?php endif; ?>

      <?php if (tienePerm('usuarios', 'ver')): ?>
        <li class="nav-item d-none d-md-block">
          <a href="index.php?url=usuario" class="nav-link">
            <i class="bi bi-people-fill me-1"></i>Usuarios
          </a>
        </li>
      <?php endif; ?>

      <?php if (tienePerm('historial', 'ver')): ?>
        <li class="nav-item d-none d-md-block">
          <a href="index.php?url=log" class="nav-link">
            <i class="bi bi-clock-history me-1"></i>Historial
          </a>
        </li>
      <?php endif; ?>
    </ul>
    <!--fin::Enlaces de Navegación de Inicio-->

    <!--inicio::Enlaces de Navegación de Fin-->
    <ul class="navbar-nav ms-auto">
      <?php require __DIR__ . '/notificaciones_panel.php'; ?>
      <?php require __DIR__ . '/profile_bubble.php'; ?>
    </ul>
    <!--fin::Enlaces de Navegación de Fin-->
  </div>
  <!--fin::Contenedor-->
</nav>
<!--fin::Cabecera-->
