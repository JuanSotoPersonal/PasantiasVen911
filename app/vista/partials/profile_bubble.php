<?php
/**
 * profile_bubble.php - Menú de Identidad de Usuario (Rediseño Minimalista)
 * 
 * Renderiza la información de sesión del usuario con una estética premium
 * basada en glassmorphism y armonía visual con el tema institucional.
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

<style>
    /* Estilos personalizados para la burbuja de perfil minimalista */
    .user-menu .nav-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .user-menu .user-image {
        width: 32px;
        height: 32px;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.2);
        margin-right: 10px;
    }

    .user-menu .dropdown-menu {
        width: 280px;
        border-radius: 15px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        padding: 0;
        margin-top: 10px !important;
    }

    .user-header-minimal {
        padding: 1.5rem;
        text-align: center;
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, rgba(255, 255, 255, 1) 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }

    .user-header-minimal img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        margin-bottom: 15px;
        border: 3px solid #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .user-header-minimal h6 {
        margin: 0;
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .user-header-minimal span {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .user-footer-minimal {
        padding: 1rem;
        background: #fff;
    }

    .btn-logout-minimal {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px;
        border-radius: 12px;
        color: #e74c3c;
        background: rgba(231, 76, 60, 0.03);
        text-decoration: none;
        font-weight: 700;
        font-size: 0.88rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(231, 76, 60, 0.15);
        letter-spacing: 0.3px;
    }

    .btn-logout-minimal i {
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .btn-logout-minimal:hover {
        background: rgba(231, 76, 60, 0.08);
        color: #c0392b;
        border-color: rgba(192, 57, 43, 0.3);
        box-shadow: 0 4px 12px rgba(192, 57, 43, 0.1);
        transform: translateY(-1px);
    }

    .btn-logout-minimal:hover i {
        transform: translateX(3px);
        color: #c0392b !important;
    }

    .btn-logout-minimal:active {
        transform: translateY(0);
        box-shadow: 0 4px 10px rgba(231, 76, 60, 0.2);
    }
</style>

<li class="nav-item dropdown user-menu">
    
    <!-- DISPARADOR: Minimalista y elegante -->
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <img src="<?= $profilePic ?>" class="user-image rounded-circle shadow-sm" alt="Perfil">
        <span class="d-none d-md-inline fw-semibold text-dark"><?= htmlspecialchars($userName) ?></span>
    </a>

    <!-- MENÚ DESPLEGABLE: Estética Premium -->
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 animate__animated animate__fadeIn animate__faster">
        
        <!-- Cuerpo del Perfil -->
        <li class="user-header-minimal">
            <img src="<?= $profilePic ?>" class="rounded-circle" alt="Avatar">
            <h6><?= htmlspecialchars($userName) ?></h6>
            <span><?= htmlspecialchars($userRol) ?></span>
        </li>

        <!-- Footer con Acción de Cierre -->
        <li class="user-footer-minimal">
            <a href="index.php?url=auth/logout" class="btn-logout-minimal">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>

</li>
