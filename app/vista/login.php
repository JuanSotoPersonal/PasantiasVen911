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

    <?php if (isset($puedeRegistrarse) && $puedeRegistrarse): ?>
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
  <?php require __DIR__ . '/partials/scripts.php'; ?>

</body>
</html>
