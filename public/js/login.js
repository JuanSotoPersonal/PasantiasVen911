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
          window.location.href = 'index.php?url=home';
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
