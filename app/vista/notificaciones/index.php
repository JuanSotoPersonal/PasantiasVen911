<?php
/**
 * VISTA: Buzón de Notificaciones
 * Propósito: Visualizar el historial completo de alertas del usuario mediante DataTables.
 */
$pageName = 'notificacion';
$seccion = 'notificacion';
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Buzón de Notificaciones</title>
    
    <!-- CABECERA GLOBALES Y ESTILOS -->
    <?php require __DIR__ . '/../partials/head.php'; ?>
    
    <!-- Estilos específicos para notificaciones -->
    <link rel="stylesheet" href="public/css/notificaciones.css" />
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />
    <style>
    .notif-row-unread {
        background-color: rgba(25, 135, 84, 0.05) !important;
        font-weight: 600;
    }
    .notif-row-read {
        background-color: transparent !important;
    }
    .notif-icon-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- COMPONENTES DE INTERFAZ GLOBAL -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main">
            <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark fw-bold">
                        <i class="bi bi-inbox-fill text-success me-2"></i>Historial de Notificaciones
                    </h1>
                </div>
                <div class="col-sm-6 text-end">
                    <button class="btn btn-outline-success shadow-sm" id="btn-marcar-todas-buzon">
                        <i class="bi bi-check2-all me-1"></i>Marcar todas como leídas
                    </button>
                </div>
            </div>
        </div>
    </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Tabla Principal -->
                <div class="card shadow-sm border-0 border-top-success">
                    <div class="card-body p-4">
                        <?php require_once __DIR__ . '/componentes/_tabla_principal.php'; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

        <!-- Pie de página Institucional -->
        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

    <!-- CARGA DE ASSETS JAVASCRIPT -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <!-- Librerías de datos (DataTables) -->
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="public/js/comun/datatables_config.js"></script>

    <!-- Script principal de DataTables para el Buzón -->
    <script src="public/js/notificaciones/index.js?v=<?= time() ?>"></script>
</body>
</html>
