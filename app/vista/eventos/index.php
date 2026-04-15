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
            <!-- Tabla de Logs -->
            <div class="card card-usuarios shadow-sm mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                  <i class="bi bi-clock-history me-2"></i>Historial de Auditoría
                </h3>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="tablaEventos" class="table table-bordered table-striped table-hover align-middle w-100">
                    <thead class="table-dark">
                      <tr>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>Registro ID</th>
                        <th>Administrador</th>
                        <th>Fecha</th>
                        <th class="text-center">Cambios</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Cargado por AJAX -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--fin::Contenido principal-->
      </main>
      <!--fin::Principal de la Aplicación-->

      <!-- MODAL PARA VER DETALLES (ESTILO VEN911) -->
      <div class="modal fade" id="modalDetallesEvento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg">
            <div class="modal-header modal-header-ven">
              <h5 class="modal-title text-white">
                <i class="bi bi-journal-text me-2"></i>Detalle de la Operación
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                   <h6 class="fw-bold text-success"><i class="bi bi-arrow-down-circle-fill me-2"></i>Valor Anterior</h6>
                   <div id="contentValorAnterior" class="p-3 bg-light border rounded json-container">
                     <!-- JSON formateado -->
                   </div>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                   <h6 class="fw-bold text-primary"><i class="bi bi-arrow-up-circle-fill me-2"></i>Valor Nuevo</h6>
                   <div id="contentValorNuevo" class="p-3 bg-light border rounded json-container">
                     <!-- JSON formateado -->
                   </div>
                </div>
              </div>
              <div class="mt-4">
                 <h6 class="fw-bold text-secondary"><i class="bi bi-info-circle-fill me-2"></i>Descripción Adicional</h6>
                 <div id="contentDetalles" class="p-3 bg-light border-start border-4 border-success rounded-end mt-1">
                    <!-- Texto de detalles -->
                 </div>
              </div>
            </div>
            <div class="modal-footer border-0">
               <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>

    </div>
    <!--fin::Contenedor de la Aplicación-->

    <!--inicio::Scripts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="public/libs/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="public/libs/popperjs/popper.min.js"></script>
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script src="public/libs/bootstrap/bootstrap.min.js"></script>
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="public/js/adminlte.js"></script>
    <!-- jQuery (requerido por DataTables) -->
    <script src="public/libs/datatables/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Lógica de la tabla de Logs -->
    <script src="public/js/tablas/eventos_datatable.js?v=1.1"></script>
    <!--fin::Scripts-->
  </body>
  <!--fin::Cuerpo-->
</html>
