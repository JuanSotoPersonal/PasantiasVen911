<!doctype html>
<html lang="es">
  <!--inicio::Encabezado-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ven911 | Historial de Sistema</title>

    <!--inicio::Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#16a34a" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#064e3b" media="(prefers-color-scheme: dark)" />
    <meta name="title" content="Ven911 | Historial de Sistema" />
    <meta name="description" content="Módulo de auditoría y registros del sistema Ven911." />
    <!--fin::Meta Tags-->

    <!--inicio::Estilos-->
    <link rel="preload" href="public/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="public/css/adminlte.css" />
    <link rel="stylesheet" href="public/css/home.css" />
    <link rel="stylesheet" href="public/css/usuarios.css" />
    <link rel="stylesheet" href="public/css/eventos.css" />
    <link rel="stylesheet" href="public/libs/source-sans-3/index.css" />
    <link rel="stylesheet" href="public/libs/overlayscrollbars/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="public/libs/bootstrap-icons/bootstrap-icons.min.css" />
    <!-- DataTables + Bootstrap 5 -->
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />
    <!--fin::Estilos-->
  </head>
  <!--fin::Encabezado-->

  <!--inicio::Cuerpo-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--inicio::Contenedor de la Aplicación-->
    <div class="app-wrapper">

      <?php require __DIR__ . '/../partials/navbar.php'; ?>
      <?php require __DIR__ . '/../partials/sidebar.php'; ?>

      <!--inicio::Principal de la Aplicación-->
      <main class="app-main">
        <!--inicio::Encabezado de Contenido-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0">Historial de Sistema</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="index.php?url=home">Inicio</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Historial</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--fin::Encabezado de Contenido-->

        <!--inicio::Contenido principal-->
        <div class="app-content">
          <div class="container-fluid">
            <?php
            // Sistema de Vistas de Dependencia (Tablas de Historial)
            if ($tabActiva === 'ficha') {
                require __DIR__ . '/componentes/_tabla_fichas.php';
            } else {
                require __DIR__ . '/componentes/_tabla_sistema.php';
            }
            ?>
          </div>
        </div>
        <!--fin::Contenido principal-->
      </main>
      <!--fin::Principal de la Aplicación-->


      <!-- ============================================================
           MODALES DEL MÓDULO
           ============================================================ -->
      <?php require __DIR__ . '/componentes/_modal_detalles.php'; ?>

    </div>
    <!--fin::Contenedor de la Aplicación-->

    <!--inicio::Scripts-->
    <!-- Scripts Globales -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <!-- jQuery & DataTables (Específicos para este módulo) -->
    <script src="public/libs/datatables/jquery-3.7.1.min.js"></script>
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Lógica de la tabla de Logs -->
    <script src="public/js/comun/datatables_config.js"></script>
    <?php if ($tabActiva === 'ficha'): ?>
        <script src="public/js/eventos/eventos_fichas_datatable.js?v=1.0"></script>
    <?php else: ?>
        <script src="public/js/eventos/eventos_datatable.js?v=1.1"></script>
    <?php endif; ?>

    <!--fin::Scripts-->
  </body>
  <!--fin::Cuerpo-->
</html>
