<?php
/**
 * Partial: Burbuja de Perfil de Usuario (User Menu)
 * Incluido en: partials/navbar.php
 */
$userName = $_SESSION['user_name'] ?? 'Invitado';
$userRol = $_SESSION['user_rol'] ?? 'S/R';
?>
<!--inicio::Menú de Usuario-->
<li class="nav-item dropdown user-menu">
  <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
    <img
      src="public/assets/img/user2-160x160.jpg"
      class="user-image rounded-circle shadow"
      alt="User Image"
    />
    <span class="d-none d-md-inline"><?= htmlspecialchars($userName) ?></span>
  </a>
  <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
    <li class="user-header text-bg-primary">
      <img
        src="public/assets/img/user2-160x160.jpg"
        class="rounded-circle shadow"
        alt="User Image"
      />
      <p>
        <?= htmlspecialchars($userName) ?>
        <small><?= htmlspecialchars($userRol) ?></small>
      </p>
    </li>
    <li class="user-footer">
      <a href="#" class="btn btn-default btn-flat">Perfil</a>
      <a href="index.php?url=auth/logout" class="btn btn-default btn-flat float-end">Salir</a>
    </li>
  </ul>
</li>
<!--fin::Menú de Usuario-->
