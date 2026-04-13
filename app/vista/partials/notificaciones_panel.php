<?php
    //--------------------------------------------------------------------
    // Partial: Panel de Notificaciones (Dropdown)
    // Incluido en: partials/navbar.php
    //--------------------------------------------------------------------
?>
<!--inicio::Notificaciones-->
<li class="nav-item dropdown notificaciones-dropdown" id="notificaciones-nav-item">
  <a class="nav-link position-relative" href="#" id="notificaciones-btn"
     data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
    <i class="bi bi-bell-fill fs-5"></i>
    <span class="badge-notif-count d-none" id="notif-badge">0</span>
  </a>
  <div class="dropdown-menu dropdown-menu-end notif-dropdown-panel shadow-lg p-0"
       aria-labelledby="notificaciones-btn">
    <!--inicio::Cabecera del panel-->
    <div class="notif-panel-header d-flex justify-content-between align-items-center px-3 py-2">
      <span class="fw-bold text-white">
        <i class="bi bi-bell me-1"></i> Notificaciones
      </span>
      <button class="btn btn-sm btn-outline-light btn-marcar-todas" id="btn-marcar-todas"
              title="Marcar todas como leídas">
        <i class="bi bi-check2-all"></i>
      </button>
    </div>
    <!--fin::Cabecera del panel-->

    <!--inicio::Lista de notificaciones-->
    <ul class="notif-lista list-unstyled mb-0" id="notif-lista">
      <li class="notif-item-vacio text-center text-muted py-4" id="notif-vacio">
        <i class="bi bi-bell-slash fs-3 d-block mb-1"></i>
        Sin notificaciones nuevas
      </li>
    </ul>
    <!--fin::Lista de notificaciones-->

    <!--inicio::Pie del panel-->
    <div class="notif-panel-footer text-center border-top py-2">
      <a href="#" class="text-success text-decoration-none small fw-semibold">
        <i class="bi bi-list-ul me-1"></i>Ver todas
      </a>
    </div>
    <!--fin::Pie del panel-->
  </div>
</li>
<!--fin::Notificaciones-->
