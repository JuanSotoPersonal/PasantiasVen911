<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title> VEN 911 Carabobo | Iniciar Sesión</title>
  <?php 
  $pageName = 'login';
  require __DIR__ . '/partials/head.php'; 
  ?>
</head>
<body>

  <!-- Elementos decorativos animados -->
  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>

  <!-- Contenedor del Login -->
  <div class="login-wrapper">
    <div class="logo-container">
      <img src="public/assets/img/ven911_logo.webp" alt="Logo VEN 911" class="logo-img">
    </div>
    
    <h1 class="login-title">VEN 911 Carabobo</h1>
    <p class="login-subtitle">Sistema Integrado de Gestión de Emergencias</p>
    
    <form id="loginForm">
      <div class="mb-4">
        <label for="usuario" class="form-label">Usuario</label>
        <div class="input-group-custom">
          <!-- TODO: Agregar icono SVG aquí si se desea -->
          <input type="text" class="form-control" id="usuario" name="usuario" placeholder="V00000000"  autocomplete="Usuario">
        </div>
        <div class="form-text mt-1 form-text-ven">
          Ingresa tu cédula (ej. V12345678) o usuario asignado.
        </div>
      </div>
      
      <div class="mb-4">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group-custom">
          <input type="password" class="form-control" id="password" name="password" placeholder="••••••••"  autocomplete="current-password">
          <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
        </div>
        <div class="form-text mt-1 form-text-ven">
          Respeta las letras mayúsculas y minúsculas.
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary btn-login">
        Ingresar al Sistema
      </button>
    </form>

    <?php if (isset($canRegister) && $canRegister): ?>
    <div class="mt-4 text-center">
      <hr class="login-hr">
      <p class="text-muted small mb-2 fw-bold">Primer inicio detectado</p>
      <a href="index.php?url=setup" class="btn btn-outline-success btn-sm w-100 py-2 border-2 fw-bold btn-setup-activation">
         <i class="bi bi-shield-lock-fill me-2"></i>Registrar y Activar Sistema
      </a>
    </div>
    <?php endif; ?>
    
    <div class="system-notice">
      Acceso restringido. Uso exclusivo para personal de respuesta a incidencias.
    </div>
  </div>

  <!-- Scripts -->
 <?php
/**
 * Partial: Scripts globales
 * Utiliza la variable $pageName para cargar condicionalmente los JS
 */
$pageName = $pageName ?? 'home';
?>
<!-- SweetAlert2 (Para notificaciones comunes) -->
<script src="public/libs/sweetalert2/sweetalert2.min.js"></script>

<?php if ($pageName === 'login'): ?>
  <!-- Scripts exclusivos del Login -->
  <script src="public/js/login.js"></script>
<?php else: ?>
  <!-- Scripts exclusivos del Dashboard / Sistema -->
  <script src="public/libs/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>
  <script src="public/libs/popperjs/popper.min.js"></script>
  <script src="public/libs/bootstrap/bootstrap.min.js"></script>
  <script src="public/js/adminlte.js"></script>

  <!-- Configuración de Scrollbars -->
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function () {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
      const isMobile = window.innerWidth <= 992;
      if (
        sidebarWrapper &&
        OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
        !isMobile
      ) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });
  </script>
<?php endif; ?>

</body>
</html>
