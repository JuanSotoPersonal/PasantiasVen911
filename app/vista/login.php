<?php
/**
 * login.php - Interfaz de Autenticación Centralizada
 * 
 * Punto de entrada visual al sistema Ven911. Gestiona el acceso de usuarios
 * mediante validación de credenciales y detecta la necesidad de configuración inicial.
 */

// 1. CONFIGURACIÓN Y ESTADOS PREVIOS
$pageName = 'login';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>VEN 911 Carabobo | Iniciar Sesión</title>
    
    <!-- 2. CABECERA Y RECURSOS GLOBALES -->
    <?php require __DIR__ . '/partials/head.php'; ?>
</head>

<body>

    <!-- 3. ELEMENTOS DECORATIVOS (Fondos Ambientales) -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <!-- 4. CONTENEDOR PRINCIPAL DE LOGIN -->
    <div class="login-wrapper">
        <!-- Identidad Institucional -->
        <div class="logo-container">
            <img src="public/assets/img/ven911_logo.webp" alt="Logo VEN 911" class="logo-img">
        </div>

        <h1 class="login-title">VEN 911 Carabobo</h1>
        <p class="login-subtitle">Sistema Integrado de Gestión de Emergencias</p>
        
        <!-- Formulario de Acceso -->
        <form id="loginForm">
            <!-- Campo: Identificación de Usuario -->
            <div class="mb-4">
                <label for="usuario" class="form-label">Usuario</label>
                <div class="input-group-custom">
                    <input type="text" class="form-control" id="usuario" name="usuario" 
                           placeholder="V00000000" autocomplete="username">
                </div>
                <div class="form-text mt-1 form-text-ven">
                    Ingresa tu cédula (ej. V12345678) o usuario asignado.
                </div>
            </div>
            
            <!-- Campo: Credencial de Seguridad -->
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group-custom">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="••••••••" autocomplete="current-password">
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                <div class="form-text mt-1 form-text-ven">
                    Respeta las letras mayúsculas y minúsculas.
                </div>
            </div>
            
            <!-- Acción Primaria -->
            <button type="submit" class="btn btn-primary btn-login">
                Ingresar al Sistema
            </button>
        </form>

        <!-- Bloque Especial: Activación de Sistema (Solo primer inicio) -->
        <?php if (isset($puedeRegistrarse) && $puedeRegistrarse): ?>
        <div class="mt-4 text-center">
            <hr class="login-hr">
            <p class="text-muted small mb-2 fw-bold">Primer inicio detectado</p>
            <a href="index.php?url=setup" class="btn btn-outline-success btn-sm w-100 py-2 border-2 fw-bold btn-setup-activation">
                <i class="bi bi-shield-lock-fill me-2"></i>Registrar y Activar Sistema
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Nota de Seguridad Legal -->
        <div class="system-notice">
            Acceso restringido. Uso exclusivo para personal de respuesta a incidencias.
        </div>
    </div>

    <!-- 5. CARGA DE LIBRERÍAS DE COMPORTAMIENTO -->
    <?php require __DIR__ . '/partials/scripts.php'; ?>

</body>
</html>

