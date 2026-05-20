<?php
/**
 * VISTA: Buzón de Notificaciones
 * Propósito: Visualizar el historial completo de alertas del usuario mediante un diseño
 * premium dividido (Split-pane layout) y filtros interactivos de última generación.
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
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- COMPONENTES DE INTERFAZ GLOBAL -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main">
            <!-- CABECERA DE MÓDULO -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark fw-bold">
                                <i class="bi bi-inbox-fill text-success me-2"></i>Buzón de Notificaciones
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO DE MÓDULO (SPLIT LAYOUT) -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row g-4">
                        
                        <!-- Panel Izquierdo: Filtros y Buscador -->
                        <div class="col-lg-3">
                            <div class="card card-notif-filters border-0 shadow-sm mb-4">
                                <div class="card-body p-3">
                                    
                                    <!-- Buscador Reactivo -->
                                    <div class="mb-4">
                                        <label class="form-label text-xs fw-bold text-uppercase text-muted mb-2">Buscar en el Buzón</label>
                                        <div class="input-group input-group-search-notif">
                                            <span class="input-group-text bg-transparent border-end-0 border-success-subtle">
                                                <i class="bi bi-search text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 border-success-subtle ps-1" id="buscador-notif" placeholder="Filtrar por palabra clave..." />
                                        </div>
                                    </div>

                                    <!-- Filtro de Estados -->
                                    <div class="mb-4">
                                        <span class="text-xs fw-bold text-uppercase text-muted d-block mb-2">Estados de Lectura</span>
                                        <ul class="nav flex-column notif-filter-list">
                                            <li class="nav-item">
                                                <a href="#" class="nav-link notif-filter-item active" data-filter="estado" data-val="todos">
                                                    <div class="d-flex align-items-center justify-content-between w-100">
                                                        <span><i class="bi bi-inbox me-2"></i>Todas</span>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill font-monospace" id="cnt-todas">-</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#" class="nav-link notif-filter-item" data-filter="estado" data-val="no-leidas">
                                                    <div class="d-flex align-items-center justify-content-between w-100">
                                                        <span><i class="bi bi-envelope-fill me-2 text-success"></i>No leídas</span>
                                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill font-monospace" id="cnt-no-leidas">-</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#" class="nav-link notif-filter-item" data-filter="estado" data-val="leidas">
                                                    <div class="d-flex align-items-center justify-content-between w-100">
                                                        <span><i class="bi bi-envelope-open me-2 text-muted"></i>Leídas</span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Filtro de Categorías -->
                                    <div class="mb-4">
                                        <span class="text-xs fw-bold text-uppercase text-muted d-block mb-2">Categorías</span>
                                        <ul class="nav flex-column notif-filter-list">
                                            <li class="nav-item">
                                                <a href="#" class="nav-link notif-filter-item" data-filter="tipo" data-val="alerta">
                                                    <span><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Alertas</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#" class="nav-link notif-filter-item" data-filter="tipo" data-val="info">
                                                    <span><i class="bi bi-info-circle-fill me-2 text-primary"></i>Información</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#" class="nav-link notif-filter-item" data-filter="tipo" data-val="cambio_estado">
                                                    <span><i class="bi bi-arrow-repeat me-2 text-success"></i>Cambios de Estado</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#" class="nav-link notif-filter-item" data-filter="tipo" data-val="critico">
                                                    <span><i class="bi bi-x-octagon-fill me-2 text-danger"></i>Críticos</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Acciones Rápidas -->
                                    <div class="pt-3 border-top">
                                        <button class="btn btn-ven-cancel w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btn-marcar-todas-buzon">
                                            <i class="bi bi-check2-all fs-6"></i>Marcar todas como leídas
                                        </button>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Panel Derecho: Listado Principal -->
                        <div class="col-lg-9">
                            <div class="card card-notif-list border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <?php require_once __DIR__ . '/componentes/_tabla_principal.php'; ?>
                                </div>
                            </div>
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
