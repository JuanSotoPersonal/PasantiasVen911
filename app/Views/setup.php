<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Activación de Sistema | VEN 911</title>
  <?php 
  $pageName = 'setup';
  require __DIR__ . '/partials/head.php'; 
  ?>
  <style>
    /* Sobrescribir el bloqueo de scroll del login.css para formularios largos */
    body {
        overflow-y: auto !important;
        display: block !important;
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
    /* El login.css usa overflow:hidden, aquí lo liberamos */
    .login-wrapper.setup-width {
        max-width: 650px;
        margin: auto;
    }
    .section-title {
        color: #166534;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        font-weight: 700;
        border-bottom: 1px solid rgba(22, 101, 52, 0.2);
        padding-bottom: 5px;
    }
    /* Asegurar que los selects se vean igual que los inputs de login.css */
    .form-select {
        background-color: #f0fdf4;
        border: 1px solid #072513;
        color: #072513;
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    .form-select:focus {
        background-color: #ffffff;
        border-color: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
        outline: none;
    }
  </style>
</head>
<body>

  <!-- Elementos decorativos animados del login -->
  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>

  <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="login-wrapper setup-width">
      <div class="logo-container">
        <img src="public/assets/img/ven911_logo.png" alt="Logo VEN 911" class="logo-img">
      </div>
      
      <h2 class="login-title">Activación de Sistema</h2>
      <p class="login-subtitle">Configura el primer administrador para comenzar a usar la plataforma.</p>
      
      <form id="setupForm" class="mt-4">
        <div class="section-title"><i class="bi bi-key-fill me-2"></i>Llave de Activación</div>
        <div class="mb-4">
          <label for="factory_code" class="form-label">Código de Fábrica (12 dígitos)</label>
          <input type="text" class="form-control" id="factory_code" name="factory_code" placeholder="XXXX-XXXX-XXXX" maxlength="12" required>
        </div>

        <div class="section-title"><i class="bi bi-person-badge-fill me-2"></i>Datos del SuperAdmin</div>
        <div class="row">
          <div class="col-md-6 mb-4">
            <label for="nombre_completo" class="form-label">Nombre Completo</label>
            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" placeholder="Ej: Juan Pérez" required>
          </div>
          <div class="col-md-6 mb-4">
            <label for="cedula" class="form-label">Cédula</label>
            <input type="text" class="form-control" id="cedula" name="cedula" placeholder="12345678" maxlength="8">
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-4">
            <label for="usuario" class="form-label">Usuario (Mín. 7 caracteres)</label>
            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="V12345678" minlength="7" required>
          </div>
          <div class="col-md-6 mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <div class="input-group-custom">
              <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" minlength="6" required>
              <i class="bi bi-eye-slash toggle-password" id="togglePassword" style="cursor: pointer;"></i>
            </div>
          </div>
        </div>

        <div class="section-title"><i class="bi bi-shield-lock-fill me-2"></i>Preguntas de Seguridad</div>
        <div class="row">
          <div class="col-md-12 mb-4">
            <label for="pregunta_1" class="form-label">Pregunta de Seguridad 1</label>
            <select class="form-select" id="pregunta_1" name="pregunta_1" required>
              <option value="">Selecciona una pregunta...</option>
              <?php foreach ($preguntas as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pregunta']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-12 mb-4">
            <label for="respuesta_1" class="form-label">Respuesta 1</label>
            <input type="text" class="form-control" id="respuesta_1" name="respuesta_1" placeholder="Tu respuesta secreta" required>
          </div>

          <div class="col-md-12 mb-4">
            <label for="pregunta_2" class="form-label">Pregunta de Seguridad 2</label>
            <select class="form-select" id="pregunta_2" name="pregunta_2" required>
              <option value="">Selecciona una pregunta...</option>
              <?php foreach ($preguntas as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pregunta']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-12 mb-4">
            <label for="respuesta_2" class="form-label">Respuesta 2</label>
            <input type="text" class="form-control" id="respuesta_2" name="respuesta_2" placeholder="Tu segunda respuesta secreta" required>
          </div>
        </div>

        <button type="submit" class="btn btn-login mt-3" id="btnSubmitSetup">
          <i class="bi bi-rocket-takeoff-fill me-2"></i>Activar Sistema y Crear Usuario
        </button>
      </form>

      <div class="system-notice mt-4">
        <a href="index.php?url=auth" class="text-decoration-none" style="color: inherit;">
          <i class="bi bi-arrow-left-circle me-1"></i> Volver al Inicio de Sesión
        </a>
      </div>
    </div>
  </div>

  <script src="public/libs/sweetalert2/sweetalert2.min.js"></script>
  <script>
    // Lógica para ver/ocultar contraseña
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });

    document.getElementById('setupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitSetup');
        const formData = new FormData(this);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

        fetch('index.php?url=setup/register', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Sistema Activado!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'index.php?url=auth';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-rocket-takeoff-fill me-2"></i>Activar Sistema y Crear Usuario';
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = 'Error de conexión';
        });
    });
  </script>
</body>
</html>
