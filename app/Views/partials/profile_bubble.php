<?php
    //--------------------------------------------------------------------
    // Partial: Burbuja de Perfil de Usuario (User Menu)
    // Incluido en: partials/navbar.php
    //--------------------------------------------------------------------
    $userName = $_SESSION['user_name'] ?? 'Invitado';
    $userRol = $_SESSION['user_rol'] ?? 'S/R';
    $rolId = (int)($_SESSION['user_rol_id'] ?? 0);

    // Mapeo de fotos de perfil según el rol
    $profilePic = 'public/assets/img/user2-160x160.jpg'; // Default
    
    switch ($rolId) {
        case 1: $profilePic = 'public/assets/img/administrador.webp'; break;
        case 2: $profilePic = 'public/assets/img/Operador.webp'; break;
        case 3: $profilePic = 'public/assets/img/Despachador.webp'; break;
        case 4: $profilePic = 'public/assets/img/Jefatura.webp'; break;
    }
?>
<!--inicio::Menú de Usuario-->
<li class="nav-item dropdown user-menu">
  <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
    <img
      src="<?= $profilePic ?>"
      class="user-image rounded-circle shadow"
      alt="User Image"
    />
    <span class="d-none d-md-inline"><?= htmlspecialchars($userName) ?></span>
  </a>
  <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
    <li class="user-header">
      <img
        src="<?= $profilePic ?>"
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
