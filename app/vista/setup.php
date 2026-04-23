<?php
/**
 * setup.php - Interfaz de Activación Primaria y Provisión de SuperAdmin
 * 
 * Este archivo gestiona la configuración inicial del sistema (First Run).
 * Permite validar la llave de producto y registrar al primer Administrador
 * con sus respectivas preguntas de seguridad para el blindaje de la cuenta.
 */

// 1. CONFIGURACIÓN INICIAL
$pageName = 'setup';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Activación de Sistema | VEN 911</title>
    
    <!-- 2. CABECERA GLOBALES (Hereda estilos de login) -->
    <?php require __DIR__ . '/partials/head.php'; ?>
</head>

<body>

    <!-- 3. ELEMENTOS DECORATIVOS (Sincronizados con el Login) -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <!-- 4. CONTENEDOR DE CONFIGURACIÓN -->
    <div class="container d-flex align-items-center justify-content-center setup-container">
        <div class="login-wrapper setup-width">
            <div class="logo-container">
                <img src="public/assets/img/ven911_logo.webp" alt="Logo VEN 911" class="logo-img">
            </div>
            
            <h2 class="login-title">Activación de Sistema</h2>
            <p class="login-subtitle">Configura el primer administrador para comenzar a usar la plataforma.</p>
            
            <!-- Formulario de Provisión -->
            <form id="setupForm" class="mt-4">
                <!-- Sección I: Validación de Licencia -->
                <div class="section-title"><i class="bi bi-key-fill me-2"></i>Llave de Activación</div>
                <div class="mb-4">
                    <label for="factory_code" class="form-label">Código de Fábrica (12 dígitos)</label>
                    <input type="text" class="form-control" id="factory_code" name="factory_code" 
                           placeholder="XXXX-XXXX-XXXX">
                    <div class="form-text mt-1 text-success-emphasis" style="font-size: 0.75rem;">
                        Código único de 12 dígitos para activar el producto.
                    </div>
                </div>

                <!-- Sección II: Identidad del SuperAdmin -->
                <div class="section-title"><i class="bi bi-person-badge-fill me-2"></i>Datos del SuperAdmin</div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="nombre_completo" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                               placeholder="Ej: Juan Pérez">
                        <div class="form-text mt-1 form-text-ven">Nombre y apellido.</div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="cedula" class="form-label">Cédula</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" placeholder="12345678">
                        <div class="form-text mt-1 form-text-ven">Solo números (6-8 dígitos).</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="usuario" class="form-label">Usuario (Mín. 7 caracteres)</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="V12345678">
                        <div class="form-text mt-1 form-text-ven">Letras y números solamente.</div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group-custom">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="••••••••" autocomplete="new-password">
                            <i class="bi bi-eye-slash toggle-password" id="togglePassword" style="cursor: pointer;"></i>
                        </div>
                        <div class="form-text mt-1 form-text-ven">
                            <i class="bi bi-info-circle me-1"></i> Mín. 6 caracteres, incluir una mayúscula y un número.
                        </div>
                    </div>
                </div>

                <!-- Sección III: Seguridad de Recuperación -->
                <div class="section-title"><i class="bi bi-shield-lock-fill me-2"></i>Preguntas de Seguridad</div>
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <label for="pregunta_1" class="form-label">Pregunta de Seguridad 1</label>
                        <select class="form-select" id="pregunta_1" name="pregunta_1">
                            <option value="">Selecciona una pregunta...</option>
                            <?php foreach ($preguntas as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pregunta']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12 mb-4">
                        <label for="respuesta_1" class="form-label">Respuesta 1</label>
                        <input type="text" class="form-control" id="respuesta_1" name="respuesta_1" 
                               placeholder="Tu respuesta secreta">
                    </div>

                    <div class="col-md-12 mb-4">
                        <label for="pregunta_2" class="form-label">Pregunta de Seguridad 2</label>
                        <select class="form-select" id="pregunta_2" name="pregunta_2">
                            <option value="">Selecciona una pregunta...</option>
                            <?php foreach ($preguntas as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pregunta']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12 mb-4">
                        <label for="respuesta_2" class="form-label">Respuesta 2</label>
                        <input type="text" class="form-control" id="respuesta_2" name="respuesta_2" 
                               placeholder="Tu segunda respuesta secreta">
                    </div>
                </div>

                <button type="submit" class="btn btn-login mt-3" id="btnSubmitSetup">
                    <i class="bi bi-rocket-takeoff-fill me-2"></i>Activar Sistema y Crear Usuario
                </button>
            </form>

            <div class="system-notice mt-4">
                <a href="index.php?url=auth" class="text-decoration-none link-inherit">
                    <i class="bi bi-arrow-left-circle me-1"></i> Volver al Inicio de Sesión
                </a>
            </div>
        </div>
    </div>

    <!-- 5. SCRIPTS DE COMPORTAMIENTO Y AJAX -->
    <script src="public/libs/sweetalert2/sweetalert2.min.js"></script>
    <script>
        // Visibilidad de contraseña
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Procesamiento asíncrono de la activación
        document.getElementById('setupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitSetup');
            const formData = new FormData(this);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Activando...';

            fetch('index.php?url=setup/registrar', {
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
                    }).then(() => window.location.href = 'index.php?url=auth');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error de Activación', text: data.message });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-rocket-takeoff-fill me-2"></i>Activar Sistema y Crear Usuario';
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Error de conexión';
            });
        });
    </script>
</body>
</html>

