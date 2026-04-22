<!doctype html>
<html lang="es">
  <!--inicio::Encabezado-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ven911 | Gestión de Usuarios</title>

    <!--inicio::Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#16a34a" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#064e3b" media="(prefers-color-scheme: dark)" />
    <meta name="title" content="Ven911 | Gestión de Usuarios" />
    <meta name="description" content="Módulo de gestión de usuarios del sistema Ven911." />
    <!--fin::Meta Tags-->

    <!--inicio::Estilos-->
    <link rel="preload" href="public/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="public/css/adminlte.css" />
    <link rel="stylesheet" href="public/css/home.css" />
    <link rel="stylesheet" href="public/css/usuarios.css" />
    <link rel="stylesheet" href="public/libs/source-sans-3/index.css" />
    <link rel="stylesheet" href="public/libs/overlayscrollbars/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="public/libs/bootstrap-icons/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="public/libs/sweetalert2/sweetalert2.min.css" />
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
                <h3 class="mb-0">Gestión de Usuarios</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="index.php?url=home">Inicio</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--fin::Encabezado de Contenido-->

        <!--inicio::Contenido de la Aplicación-->
        <div class="app-content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <!--inicio::Sistema de Vistas de Dependencia (Tablas)-->
                <?php
                // Determinar qué tabla mostrar según el parámetro GET 't'
                $tabActiva = $_GET['t'] ?? 'todos';

                if ($tabActiva === 'todos') {
                    require __DIR__ . '/componentes/_tabla_principal.php';
                } elseif ($tabActiva === 'inactivos') {
                    require __DIR__ . '/componentes/_tabla_inactivos.php';
                } elseif (strpos($tabActiva, 'rol_') === 0) {
                    $rolActivoId = (int)str_replace('rol_', '', $tabActiva);
                    require __DIR__ . '/componentes/_tablas_roles.php';
                } else {
                    // Por si ingresan una ruta inválida
                    require __DIR__ . '/componentes/_tabla_principal.php';
                }
                ?>
                <!--fin::Sistema de Vistas de Dependencia (Tablas)-->


              </div>
            </div>
          </div>
        </div>
        <!--fin::Contenido de la Aplicación-->
      </main>
      <!--fin::Principal de la Aplicación-->

      <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>
    <!--fin::Contenedor de la Aplicación-->


    <!-- ============================================================
         MODALES DEL MÓDULO
         ============================================================ -->
    <?php if (tienePerm('usuarios', 'crear')): ?>
        <?php require __DIR__ . '/componentes/_modal_crear.php'; ?>
    <?php endif; ?>

    <?php if (tienePerm('usuarios', 'editar')): ?>
        <?php require __DIR__ . '/componentes/_modal_editar.php'; ?>
        <?php require __DIR__ . '/componentes/_modal_cambiar_password.php'; ?>
    <?php endif; ?>

    <?php if ($_SESSION['user_rol_id'] == 1): ?>
        <?php require __DIR__ . '/componentes/_modal_config_seguridad.php'; ?>
    <?php endif; ?>


    <!--inicio::Scripts-->
    <!-- Scripts Globales -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <!-- jQuery & DataTables (Específicos para este módulo) -->
    <script src="public/libs/datatables/jquery-3.7.1.min.js"></script>
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Exportar Permisos a JS -->
    <script>
        window.VEN911_PERM_CREAR  = <?= tienePerm('usuarios', 'crear')  ? 'true' : 'false' ?>;
        window.VEN911_PERM_EDITAR = <?= tienePerm('usuarios', 'editar') ? 'true' : 'false' ?>;
        window.USER_ROL_ID        = <?= (int)$_SESSION['user_rol_id'] ?>;
    </script>

    <!--inicio::Configuración de OverlayScrollbars-->
    <script src="public/js/usuarios/overlay.js"></script>
    <!--fin::Configuración de OverlayScrollbars-->

    <!--inicio::Módulo Usuarios JS-->
    <script src="public/js/comun/datatables_config.js"></script>
    <script src="public/js/usuarios/usuarios_datatable.js"></script>
    <script src="public/js/usuarios/usuarios_roles.js"></script>
    <!--fin::Módulo Usuarios JS-->
    <!--fin::Scripts-->

  </body>
  <!--fin::Cuerpo-->
</html>
