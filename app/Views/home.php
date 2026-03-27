<!doctype html>
<html lang="en">
  <!--inicio::Encabezado-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ven911 | Dashboard</title>

    <!--inicio::Meta Tags de Accesibilidad-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#16a34a" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#064e3b" media="(prefers-color-scheme: dark)" />
    <!--fin::Meta Tags de Accesibilidad-->

    <!--inicio::Meta Tags Primarios-->
    <meta name="title" content="Ven911 | Dashboard" />
    <meta name="author" content="Ven911 Development Team" />
    <meta name="description" content="Sistema de gestión Ven911." />
    <!--fin::Meta Tags Primarios-->

    <!--inicio::Funciones de Accesibilidad-->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="public/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="public/css/adminlte.css" />
    <!-- Personalización del Tema VEN 911 -->
    <link rel="stylesheet" href="public/css/home.css" />
    <!--fin::Funciones de Accesibilidad-->

    <!--inicio::Fuentes-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--fin::Fuentes-->

    <!--inicio::Plugin de Terceros(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--fin::Plugin de Terceros(OverlayScrollbars)-->

    <!--inicio::Plugin de Terceros(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--fin::Plugin de Terceros(Bootstrap Icons)-->

    <!--inicio::Plugin Requerido(AdminLTE)-->
    <!-- Ya se carga en el head con personalizaciones -->
    <!--fin::Plugin Requerido(AdminLTE)-->

    <!-- CSS específico de la página -->
  </head>
  <!--fin::Encabezado-->

  <!--inicio::Cuerpo-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--inicio::Contenedor de la Aplicación-->
    <div class="app-wrapper">

      <?php require __DIR__ . '/partials/navbar.php'; ?>

      <?php require __DIR__ . '/partials/sidebar.php'; ?>

      
<!--inicio::Principal de la Aplicación -->
<main class="app-main">
  <!--inicio::Encabezado de Contenido de la Aplicación-->
  <div class="app-content-header">
    <!--inicio::Contenedor-->
    <div class="container-fluid">
      <!--inicio::Fila-->
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Panel de Control</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Panel</li>
          </ol>
        </div>
      </div>
      <!--fin::Fila-->
    </div>
    <!--fin::Contenedor-->
  </div>
  <!--fin::Encabezado de Contenido de la Aplicación-->

  <!--inicio::Contenido de la Aplicación-->
  <div class="app-content">
    <!--inicio::Contenedor-->
    <div class="container-fluid">
      <!--inicio::Fila-->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Bienvenido</h3>
            </div>
            <div class="card-body">
              Tu sistema Ven911 está listo para ser desarrollado.
            </div>
          </div>
        </div>
      </div>
      <!--fin::Fila-->
    </div>
    <!--fin::Contenedor-->
  </div>
  <!--fin::Contenido de la Aplicación-->
</main>
<!--fin::Principal de la Aplicación-->

      <?php require __DIR__ . '/partials/footer.php'; ?>

    </div>
    <!--fin::Contenedor de la Aplicación-->

    <!--inicio::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="public/js/adminlte.js"></script>
    <!--fin::Plugin Requerido(AdminLTE)-->

    <!--inicio::Configuración de OverlayScrollbars-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--fin::Configuración de OverlayScrollbars-->

    <!-- Agrega aquí los scripts específicos de tu página -->
    <!--fin::Script-->
  </body>
  <!--fin::Cuerpo-->
</html>
