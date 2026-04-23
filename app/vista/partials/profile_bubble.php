<?php
/**
 * profile_bubble.php - Menú de Identidad de Usuario
 * 
 * Renderiza la información de sesión del usuario autenticado, su avatar
 * institucional según el rol y las opciones de gestión de cuenta.
 */

// 1. CARGA DE METADATOS DE SESIÓN
$userName   = $_SESSION['user_name'] ?? 'Invitado';
$userRol    = $_SESSION['user_rol'] ?? 'S/R';
$rolId      = (int)($_SESSION['user_rol_id'] ?? 0);

// Mappeo institucional de avatares según jerarquía
$profilePic = match($rolId) {
    1       => 'public/assets/img/administrador.webp',
    2       => 'public/assets/img/Operador.webp',
    3       => 'public/assets/img/Despachador.webp',
    4       => 'public/assets/img/Jefatura.webp',
    default => 'public/assets/img/user2-160x160.jpg'
};
?>

<li class="nav-item dropdown user-menu">
    
    <!-- 2. DISPARADOR (AVATAR Y NOMBRE) -->
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <img src="<?= $profilePic ?>" class="user-image rounded-circle shadow" alt="Usuario">
        <span class="d-none d-md-inline fw-semibold"><?= htmlspecialchars($userName) ?></span>
    </a>

    <!-- 3. MENÚ DESPLEGABLE DE SESIÓN -->
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow border-0">
        <!-- Encabezado de Perfil -->
        <li class="user-header bg-success-subtle">
            <img src="<?= $profilePic ?>" class="rounded-circle shadow-sm border border-2 border-white" alt="Avatar">
            <p class="text-success-emphasis fw-bold">
                <?= htmlspecialchars($userName) ?>
                <small class="d-block text-secondary fw-normal"><?= htmlspecialchars($userRol) ?></small>
            </p>
        </li>

        <!-- Acciones de Cuenta -->
        <li class="user-footer bg-white p-3">
            <div class="d-grid">
                <a href="index.php?url=auth/logout" class="btn btn-outline-danger btn-sm border-2 fw-bold">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                </a>
            </div>
        </li>
    </ul>

</li>

