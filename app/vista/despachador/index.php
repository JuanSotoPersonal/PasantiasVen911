<?php
/**
 * index.php - Centro de Despacho VEN 911
 * 
 * Panel operativo para la asignación y seguimiento de organismos de respuesta
 * sobre fichas de emergencia activas. Accesible por Despachador y Administrador.
 */

$pageName = 'despacho';
$tabActiva = $_GET['t'] ?? 'general';
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Centro de Despacho</title>

    <?php require __DIR__ . '/../partials/head.php'; ?>

    <link rel="stylesheet" href="public/css/despacho.css?v=1.0" />
    <link rel="stylesheet" href="public/css/fichas.css?v=1.2" />
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <div class="app-wrapper">

        <!-- Componentes globales de interfaz -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main">

            <!-- Encabezado de sección -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0 fw-bold">Centro de Despacho</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="index.php?url=home">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Centro de Despacho</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="app-content">
                <div class="container-fluid">
                    <?php require __DIR__ . '/componentes/_tabla_despachos.php'; ?>
                </div>
            </div>

        </main>

        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

    <!-- Modales (controladas por permisos RBAC) -->
    <?php if (tienePerm('despachos', 'crear')): ?>
        <?php require __DIR__ . '/componentes/_modal_asignar.php'; ?>
    <?php endif; ?>

    <?php if (tienePerm('despachos', 'editar')): ?>
        <?php require __DIR__ . '/componentes/_modal_editar.php'; ?>
    <?php endif; ?>

    <!-- Modal de edición completa de ficha (reutilizado del módulo de fichas, sin duplicar) -->
    <?php if (tienePerm('fichas', 'editar')): ?>
        <?php require __DIR__ . '/../fichas/componentes/_modal_editar.php'; ?>
    <?php endif; ?>

    <?php require __DIR__ . '/componentes/_modal_detalle.php'; ?>



    <!-- Motor JavaScript y librerías -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Transmisión de flags de permisos RBAC al contexto JavaScript -->
    <script>
        window.VEN911_PERM_DESPACHO_CREAR  = <?= tienePerm('despachos', 'crear')          ? 'true' : 'false' ?>;
        window.VEN911_PERM_DESPACHO_EDITAR = <?= tienePerm('despachos', 'editar')          ? 'true' : 'false' ?>;
        window.VEN911_PERM_DESPACHO_ESTADO = <?= tienePerm('despachos', 'cambiar_estado')  ? 'true' : 'false' ?>;
        window.VEN911_PERM_FICHA_EDITAR    = <?= tienePerm('fichas', 'editar')             ? 'true' : 'false' ?>;
        window.VEN911_PERM_FICHA_ESTADO    = <?= tienePerm('fichas', 'cambiar_estado')     ? 'true' : 'false' ?>;

        // Alias requeridos por la lógica del modal de edición de ficha reutilizado
        window.VEN911_PERM_EDITAR         = window.VEN911_PERM_FICHA_EDITAR;
        window.VEN911_PERM_CAMBIAR_ESTADO = window.VEN911_PERM_FICHA_ESTADO;
    </script>



    <script src="public/js/comun/datatables_config.js"></script>
    <script src="public/js/despacho/despacho_datatable.js?v=1.1"></script>



</body>
</html>
