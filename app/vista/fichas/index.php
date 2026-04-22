<!doctype html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ven911 | Fichas de Emergencia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="description" content="Módulo de gestión de fichas de emergencia del sistema Ven911." />

    <link rel="preload" href="public/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="public/css/adminlte.css" />
    <link rel="stylesheet" href="public/css/home.css" />
    <link rel="stylesheet" href="public/css/fichas.css?v=1.1" />
    <link rel="stylesheet" href="public/libs/source-sans-3/index.css" />
    <link rel="stylesheet" href="public/libs/overlayscrollbars/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="public/libs/bootstrap-icons/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="public/libs/sweetalert2/sweetalert2.min.css" />
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />
  </head>

  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

      <?php require __DIR__ . '/../partials/navbar.php'; ?>
      <?php require __DIR__ . '/../partials/sidebar.php'; ?>

      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0">
                  <?= $tabActiva === 'configuracion' ? 'Configuración del Sistema' : 'Fichas de Emergencia' ?>
                </h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="index.php?url=home">Inicio</a></li>
                  <li class="breadcrumb-item"><a href="index.php?url=ficha">Fichas</a></li>
                  <?php if ($tabActiva === 'configuracion'): ?>
                    <li class="breadcrumb-item active">Configuración</li>
                  <?php else: ?>
                    <li class="breadcrumb-item active">Listado</li>
                  <?php endif; ?>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <?php
            if ($tabActiva === 'configuracion' && tienePerm('configuracion', 'gestionar')) {
                require __DIR__ . '/componentes/_configuracion.php';
            } else {
                $estadoFiltro = match($tabActiva) {
                    'pendientes' => 'Pendiente',
                    'en_proceso' => 'En Proceso',
                    'cerradas'   => 'Cerrado',
                    default      => 'todos',
                };
                require __DIR__ . '/componentes/_tabla_fichas.php';
            }
            ?>
          </div>
        </div>

      </main>
    </div>

    <?php if ($tabActiva !== 'configuracion'): ?>
      <?php if (tienePerm('fichas', 'crear')): ?>
        <?php require __DIR__ . '/componentes/_modal_crear.php'; ?>
      <?php endif; ?>
      <?php if (tienePerm('fichas', 'editar')): ?>
        <?php require __DIR__ . '/componentes/_modal_editar.php'; ?>
      <?php endif; ?>
      <?php require __DIR__ . '/componentes/_modal_detalle.php'; ?>
    <?php endif; ?>

    <script src="public/libs/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>
    <script src="public/libs/popperjs/popper.min.js"></script>
    <script src="public/libs/bootstrap/bootstrap.min.js"></script>
    <script src="public/js/adminlte.js"></script>
    <script src="public/libs/datatables/jquery-3.7.1.min.js"></script>
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="public/libs/sweetalert2/sweetalert2.min.js"></script>

    <script>
        window.VEN911_PERM_EDITAR         = <?= tienePerm('fichas', 'editar')         ? 'true' : 'false' ?>;
        window.VEN911_PERM_CAMBIAR_ESTADO  = <?= tienePerm('fichas', 'cambiar_estado')  ? 'true' : 'false' ?>;
    </script>

    <script src="public/js/tablas/datatables_config.js"></script>
    <?php if ($tabActiva === 'configuracion'): ?>
      <script src="public/js/tablas/fichas_configuracion.js"></script>
    <?php else: ?>
      <script src="public/js/tablas/fichas_datatable.js?v=1.0"></script>
    <?php endif; ?>
  </body>
</html>
