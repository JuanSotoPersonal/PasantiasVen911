/* 
 * Lógica compartida para el Dashboard Principal
 * Configuración de scrollbars y utilidades de UI
 */
const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-scroll-area';
const DefaultLayout = {
  scrollbarTheme: 'os-theme-light',
  scrollbarAutoHide: 'leave',
  scrollbarClickScroll: true,
};

document.addEventListener('DOMContentLoaded', function () {
  const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

  // Deshabilitar scrollbars personalizados en móviles para evitar interferencias
  const isMobile = window.innerWidth <= 992;

  if (
    sidebarWrapper &&
    OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
    !isMobile
  ) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
      overflow: {
        x: 'hidden',   // Bloquear scroll horizontal
        y: 'scroll',   // Solo scroll vertical
      },
      scrollbars: {
        theme: DefaultLayout.scrollbarTheme,
        autoHide: DefaultLayout.scrollbarAutoHide,
        clickScroll: DefaultLayout.scrollbarClickScroll,
      },
    });
  }
});
