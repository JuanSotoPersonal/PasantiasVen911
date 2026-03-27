document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const usuario = document.getElementById("usuario").value;
    const password = document.getElementById("password").value;
    
    if (usuario.trim().length < 8) {
      Swal.fire({
        icon: 'warning',
        title: 'Cédula Inválida',
        text: 'La cédula debe contener al menos 8 dígitos.',
        confirmButtonColor: '#2563eb'
      });
      return;
    }
    
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
        confirmButtonColor: '#2563eb',
        background: '#1e293b',
        color: '#fff'
      });
    }
  });
});
