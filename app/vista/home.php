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
    <!-- Módulo de Notificaciones -->
    <link rel="stylesheet" href="public/css/notificaciones.css" />
    <!--fin::Funciones de Accesibilidad-->

    <!--inicio::Fuentes-->
    <link
      rel="stylesheet"
      href="public/libs/source-sans-3/index.css"
    />
    <!--fin::Fuentes-->

    <!--inicio::Plugin de Terceros(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="public/libs/overlayscrollbars/overlayscrollbars.min.css"
    />
    <!--fin::Plugin de Terceros(OverlayScrollbars)-->

    <!--inicio::Plugin de Terceros(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="public/libs/bootstrap-icons/bootstrap-icons.min.css"
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
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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

      <div class="row">
        <div class="col-md-6 mt-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
              <h3 class="card-title text-success fw-bold">
                <i class="bi bi-pie-chart-fill me-2"></i>Distribución de Usuarios
              </h3>
            </div>
            <div class="card-body">
              <div id="usuariosChart"></div>
            </div>
          </div>
        </div>
      </div>
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
    <script src="public/libs/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="public/libs/popperjs/popper.min.js"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script src="public/libs/bootstrap/bootstrap.min.js"></script>
    <!--end::Required Plugin(Bootstrap 5)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="public/js/adminlte.js"></script>
    <!--fin::Plugin Requerido(AdminLTE)-->

    <!--inicio::Configuración de OverlayScrollbars-->
    <script src="public/js/home_dashboard.js"></script>
    <!--fin::Configuración de OverlayScrollbars-->

    <!-- Módulo de Notificaciones (SSE) -->
    <script src="public/js/notificaciones.js"></script>
    <!--fin::Notificaciones-->

    <!-- ApexCharts -->
    <script src="public/libs/apexcharts/apexcharts.min.js"></script>
    
    <!-- Script de la Gráfica de Usuarios -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const stats = <?php echo json_encode($datos); ?>;
        
        const options = {
          series: [stats.activo, stats.inactivo],
          chart: {
            type: 'donut',
            height: 350,
            fontFamily: 'inherit'
          },
          labels: ['Activos', 'Inactivos'],
          colors: ['#16a34a', '#dc2626'], // Verde corporativo y Rojo alerta
          legend: {
            position: 'bottom'
          },
          stroke: {
            show: false
          },
          plotOptions: {
            pie: {
              donut: {
                labels: {
                  show: true,
                  total: {
                    show: true,
                    label: 'Total Usuarios',
                    color: '#064e3b',
                    formatter: function (w) {
                      return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                    }
                  }
                }
              }
            }
          },
          responsive: [{
            breakpoint: 480,
            options: {
              chart: {
                width: 200
              },
              legend: {
                position: 'bottom'
              }
            }
          }]
        };

        const chart = new ApexCharts(document.querySelector("#usuariosChart"), options);
        chart.render();
      });
    </script>

    <!-- Agrega aquí los scripts específicos de tu página -->
    <!--fin::Script-->
  </body>
  <!--fin::Cuerpo-->
</html>
