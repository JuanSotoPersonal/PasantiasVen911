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
  
  <style>
    /* Diseño Base / Estilos Globales */
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      /* Fondo oscuro premium con un leve gradiente */
      background: linear-gradient(135deg, #090e17 0%, #1e293b 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      overflow: hidden;
    }

    /* Formas decorativas animadas de fondo */
    .bg-shape {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      z-index: 0;
      animation: float 10s infinite alternate;
    }
    .shape-1 {
      width: 400px;
      height: 400px;
      background: rgba(59, 130, 246, 0.2);
      top: -100px;
      left: -100px;
    }
    .shape-2 {
      width: 500px;
      height: 500px;
      background: rgba(168, 85, 247, 0.15);
      bottom: -150px;
      right: -100px;
      animation-delay: -5s;
    }
    @keyframes float {
      0% { transform: translateY(0) scale(1); }
      100% { transform: translateY(50px) scale(1.1); }
    }

    /* Tarjeta de Login - Efecto Glassmorphism */
    .login-wrapper {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 420px;
      padding: 3rem 2.5rem;
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 1.5rem;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      animation: fadeIn 0.8s ease-out forwards;
      opacity: 0;
      transform: translateY(20px);
    }
    @keyframes fadeIn {
      to { opacity: 1; transform: translateY(0); }
    }

    /* Estilos del Logo */
    .logo-container {
      text-align: center;
      margin-bottom: 2rem;
    }
    .logo-img {
      max-width: 110px;
      filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4));
      transition: transform 0.3s ease;
    }
    .logo-img:hover {
      transform: scale(1.05);
    }

    /* Títulos */
    .login-title {
      font-weight: 700;
      font-size: 1.5rem;
      letter-spacing: -0.5px;
      margin-bottom: 0.5rem;
      text-align: center;
    }
    .login-subtitle {
      font-weight: 400;
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      text-align: center;
      margin-bottom: 2rem;
    }

    /* Estilos de los inputs */
    .form-label {
      font-size: 0.85rem;
      font-weight: 500;
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 0.4rem;
    }
    .input-group-custom {
      position: relative;
    }
    .form-control {
      background: rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #fff;
      border-radius: 0.75rem;
      padding: 0.85rem 1rem;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }
    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.3);
    }
    .form-control:focus {
      background: rgba(0, 0, 0, 0.3);
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
      color: #fff;
    }

    /* Botón de Acceso */
    .btn-login {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border: none;
      border-radius: 0.75rem;
      padding: 0.85rem;
      font-weight: 600;
      font-size: 1rem;
      letter-spacing: 0.5px;
      width: 100%;
      margin-top: 1.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4), 0 2px 4px -1px rgba(37, 99, 235, 0.2);
    }
    .btn-login:hover {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.5), 0 4px 6px -2px rgba(37, 99, 235, 0.3);
    }
    .btn-login:active {
      transform: translateY(0);
    }

    /* Alerta Inferior */
    .system-notice {
      text-align: center;
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.4);
      margin-top: 2rem;
    }
    
    /* Configuración SweetAlert para que combine con dark theme */
    div:where(.swal2-container) div:where(.swal2-popup) {
      background: #1e293b !important;
      color: #fff !important;
      border-radius: 1rem !important;
      border: 1px solid rgba(255,255,255,0.1) !important;
    }
  </style>
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
        <label for="email" class="form-label">Correo Electrónico</label>
        <div class="input-group-custom">
          <!-- TODO: Agregar icono SVG aquí si se desea -->
          <input type="email" class="form-control" id="email" name="email" placeholder="operador@ven911.gob.ve" required autocomplete="email">
        </div>
      </div>
      
      <div class="mb-4">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group-custom">
          <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
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
  
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const loginForm = document.getElementById("loginForm");
      
      loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;
        
        // 1. Mostrar pantalla de carga
        Swal.fire({
          title: 'Autenticando',
          html: 'Iniciando conexión segura...',
          allowEscapeKey: false,
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        try {
          // 2. Aquí iría el fetch real hacia el controlador:
          // const response = await fetch('index.php?controller=auth&action=login', {
          //   method: 'POST',
          //   body: new FormData(loginForm)
          // });
          // const data = await response.json();
          
          // Simulamos un retraso de red de 1.5s
          await new Promise(resolve => setTimeout(resolve, 1500));
          
          // Validación frontend temporal para exhibición (El backend usará password_verify)
          if(email.includes('ven911') && password.length >= 6) {
            Swal.fire({
              icon: 'success',
              title: 'Acceso Autorizado',
              text: 'Bienvenido al sistema VEN 911.',
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              // Simular la recarga/redirección hacia el index que maneje los controladores
              // window.location.href = 'index.php?route=dashboard';
              console.log("Redirigiendo...");
            });
          } else {
            throw new Error('Credenciales inválidas. Intente nuevamente.');
          }
          
        } catch (error) {
          // Manejo de errores de credenciales o de servidor
          Swal.fire({
            icon: 'error',
            title: 'Acceso Denegado',
            text: error.message || 'Ocurrió un error en la conexión.',
            confirmButtonColor: '#2563eb',
            background: '#1e293b',
            color: '#fff'
          });
        }
      });
    });
  </script>
</body>
</html>
