<!doctype html>
<html lang="en">
  <!--inicio::Encabezado-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ven911 | Dashboard</title>

    <!--inicio::Meta Tags de Accesibilidad-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--fin::Meta Tags de Accesibilidad-->

    <!--inicio::Meta Tags Primarios-->
    <meta name="title" content="Ven911 | Dashboard" />
    <meta name="author" content="Ven911 Development Team" />
    <meta name="description" content="Sistema de gestión Ven911." />
    <!--fin::Meta Tags Primarios-->

    <!--inicio::Funciones de Accesibilidad-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="public/css/adminlte.css" as="style" />
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
    <link rel="stylesheet" href="public/css/adminlte.css" />
    <!--fin::Plugin Requerido(AdminLTE)-->

    <!-- CSS específico de la página -->
  </head>
  <!--fin::Encabezado-->
  <!--inicio::Cuerpo-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--inicio::Contenedor de la Aplicación-->
    <div class="app-wrapper">
      <!--inicio::Cabecera-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--inicio::Contenedor-->
        <div class="container-fluid">
          <!--inicio::Enlaces de Navegación de Inicio-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="#" class="nav-link">Home</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="#" class="nav-link">Contact</a>
            </li>
          </ul>
          <!--fin::Enlaces de Navegación de Inicio-->

          <!--inicio::Enlaces de Navegación de Fin-->
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <div class="navbar-search-block">
                <form class="form-inline">
                  <div class="input-group input-group-sm">
                    <input
                      class="form-control form-control-navbar"
                      type="search"
                      placeholder="Buscar"
                      aria-label="Search"
                    />
                    <div class="input-group-append">
                      <button class="btn btn-navbar" type="submit">
                        <i class="bi bi-search"></i>
                      </button>
                      <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>
            <!--fin::Buscador en el Navbar-->

            <!-- Menú de Usuario -->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="public/assets/img/user2-160x160.jpg"
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline">Usuario Ven911</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img
                    src="public/assets/img/user2-160x160.jpg"
                    class="rounded-circle shadow"
                    alt="User Image"
                  />
                  <p>
                    Usuario Ven911
                    <small>Miembro desde 2024</small>
                  </p>
                </li>
                <li class="user-footer">
                  <a href="#" class="btn btn-default btn-flat">Perfil</a>
                  <a href="#" class="btn btn-default btn-flat float-end">Salir</a>
                </li>
              </ul>
            </li>
            <!--fin::Menú de Usuario-->
          </ul>
          <!--fin::Enlaces de Navegación de Fin-->
        </div>
        <!--fin::Contenedor-->
      </nav>
      <!--fin::Cabecera-->
      <!--inicio::Barra Lateral-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--inicio::Marca de la Barra Lateral-->
        <div class="sidebar-brand">
          <!--inicio::Enlace de Marca-->
          <a href="./index.html" class="brand-link">
            <!--inicio::Imagen de Marca-->
            <img
              src="public/assets/img/AdminLTELogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--fin::Imagen de Marca-->
            <!--inicio::Texto de Marca-->
            <span class="brand-text fw-light">Ven 911</span>
            <!--fin::Texto de Marca-->
          </a>
          <!--fin::Enlace de Marca-->
        </div>
        <!--fin::Marca de la Barra Lateral-->
        <!--inicio::Envoltorio de la Barra Lateral-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--inicio::Menú de la Barra Lateral-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-item">
                <a href="#" class="nav-link active">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>Dashboard</p>
                </a>
              </li>
            </ul>
            <!--fin::Menú de la Barra Lateral-->
          </nav>
        </div>
        <!--fin::Envoltorio de la Barra Lateral-->
      </aside>
      <!--fin::Barra Lateral-->
      <!--inicio::Principal de la Aplicación-->
      <main class="app-main">
        <!--inicio::Encabezado de Contenido de la Aplicación-->
        <div class="app-content-header">
          <!--begin::Container-->
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
          <!--begin::Container-->
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
      <!--inicio::Pie de Página-->
      <footer class="app-footer">
        <!--inicio::Al final-->
        <div class="float-end d-none d-sm-inline">Personalízalo aquí</div>
        <!--fin::Al final-->
        <!--inicio::Copyright-->
        <strong>
          Copyright &copy; 2024&nbsp;
          <a href="#" class="text-decoration-none">Ven911</a>.
        </strong>
        Todos los derechos reservados.
        <!--fin::Copyright-->
      </footer>
      <!--fin::Pie de Página-->
    </div>
    <!--fin::Contenedor de la Aplicación-->
    <!--inicio::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="public/js/adminlte.js"></script>
    <!--fin::Plugin Requerido(AdminLTE)--><!--inicio::Configuración de OverlayScrollbars-->
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
