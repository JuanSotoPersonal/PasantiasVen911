document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", function () {
      // Toggle the type attribute
      const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);

      // Toggle the eye icon
      this.classList.toggle("bi-eye");
      this.classList.toggle("bi-eye-slash");
    });
  }

  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value;
    const password = document.getElementById("password").value;

    // 1. Validar campos obligatorios básicos en UI (opcional pero ahorra red)
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

    // 3. Mostrar pantalla de carga
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
      // Petición real al backend
      const response = await fetch('index.php?url=auth/authenticate', {
        method: 'POST',
        headers: {
          // Si envías FormData no necesitas Content-Type
        },
        body: new FormData(loginForm)
      });

      const data = await response.json();

      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Acceso Autorizado',
          text: data.message || 'Bienvenido al sistema VEN 911.',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          // Redireccionar al Dashboard Principal
          window.location.href = 'index.php?url=home';
        });
      } else {
        throw new Error(data.message || 'Credenciales inválidas. Intente nuevamente.');
      }

    } catch (error) {
      // Manejo de errores de credenciales o de servidor
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
