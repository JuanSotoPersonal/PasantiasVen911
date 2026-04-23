/**
 * login.js - Gestión de Autenticación (Ven911)
 * 
 * Controla la interacción del formulario de acceso, validaciones en cliente,
 * visibilidad de credenciales y comunicación asíncrona con el servidor.
 */

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. REFERENCIAS AL DOM Y VARIABLES NUCLEARES
    const loginForm      = document.getElementById("loginForm");
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput  = document.getElementById("password");

    // 2. INTERACCIÓN DE UI: VISIBILIDAD DE CONTRASEÑA
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener("click", function () {
            // Alternar el atributo type del input
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);

            // Alternar el estado visual del icono (ojo abierto/cerrado)
            this.classList.toggle("bi-eye");
            this.classList.toggle("bi-eye-slash");
        });
    }

    // 3. GESTIÓN DE AUTENTICACIÓN (LOGIN ASÍNCRONO)
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const usuario  = document.getElementById("usuario").value;
        const password = document.getElementById("password").value;

        // 3.1 Validaciones tácticas en el cliente (Inercia Cero)
        if (usuario.trim() === '' || password.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Campos Requeridos',
                text: 'Por favor, ingrese usuario y contraseña.',
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-login' }
            });
            return;
        }

        // 3.2 Feedback visual: Pantalla de carga (Wait UI)
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
            // 3.3 Petición HTTP al backend (Controlador de Autenticación)
            const response = await fetch('index.php?url=auth/authenticate', {
                method: 'POST',
                body: new FormData(loginForm)
            });

            const data = await response.json();

            // 3.4 Evaluación de respuesta exitosa
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Acceso Autorizado',
                    text: data.message || 'Bienvenido al sistema VEN 911.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Redirección al Dashboard Principal tras sesión válida
                    window.location.href = 'index.php?url=home';
                });
            } else {
                // Falla lógica (credenciales incorrectas)
                throw new Error(data.message || 'Credenciales inválidas. Intente nuevamente.');
            }

        } catch (error) {
            // 3.5 Gestión de excepciones y errores de red
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: error.message || 'Ocurrió un error en la conexión.',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-login'
                }
            });
        }
    });
});
