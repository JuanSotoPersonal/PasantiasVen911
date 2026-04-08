// usuarios/overlay.js
// Inicializa OverlayScrollbars en el sidebar del módulo de Usuarios
document.addEventListener('DOMContentLoaded', function () {
  const sidebarWrapper = document.querySelector('.sidebar-wrapper');
  const isMobile = window.innerWidth <= 992;
  if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
      scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true },
    });
  }
});
