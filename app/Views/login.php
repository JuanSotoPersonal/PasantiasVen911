<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>VEN 911 | Iniciar Sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Google Fonts: Inter para un look moderno -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Estilos propios del Login -->
  <link rel="stylesheet" href="public/css/login.css">
</head>
<body>

  <!-- Elementos decorativos animados -->
  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>

  <!-- Contenedor del Login -->
  <div class="login-wrapper">
    <div class="logo-container">
      <img src="public/assets/img/ven911_logo.png" alt="Logo VEN 911" class="logo-img">
    </div>
    
    <h1 class="login-title">VEN 911</h1>
    <p class="login-subtitle">Sistema Integrado de Gestión de Emergencias</p>
    
    <form id="loginForm">
      <div class="mb-4">
        <label for="usuario" class="form-label">Usuario</label>
        <div class="input-group-custom">
          <!-- TODO: Agregar icono SVG aquí si se desea -->
          <input type="text" class="form-control" id="usuario" name="usuario" placeholder="V00000000"  autocomplete="Usuario">
        </div>
      </div>
      
      <div class="mb-4">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group-custom">
          <input type="password" class="form-control" id="password" name="password" placeholder="••••••••"  autocomplete="current-password">
          <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary btn-login">
        Ingresar al Sistema
      </button>
    </form>
    
    <div class="system-notice">
      Acceso restringido. Uso exclusivo para personal de respuesta a incidencias.
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Lógica y manejo del flujo del Login -->
  <script src="public/js/login.js"></script>
</body>
</html>
