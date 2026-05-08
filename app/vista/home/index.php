<?php
/**
 * index.php - Vista Principal (Dashboard)
 * 
 * Este archivo actúa como contenedor principal del panel de control.
 * Delega la renderización de widgets y gráficas a componentes modulares.
 */

// 1. CONFIGURACIÓN DE PÁGINA
$pageName = 'home';
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Dashboard Central</title>
    
    <!-- 2. CABECERA GLOBALES Y ESTILOS -->
    <?php require __DIR__ . '/../partials/head.php'; ?>
    
    <!-- Estilos específicos para notificaciones push y diseño home -->
    <link rel="stylesheet" href="public/css/notificaciones.css" />
    <link rel="stylesheet" href="public/css/fichas.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    
    <div class="app-wrapper">

        <!-- 3. COMPONENTES DE INTERFAZ GLOBAL -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- 4. CONTENIDO PRINCIPAL (APP-MAIN) -->
        <main class="app-main">
            
            <!-- Encabezado Hero: Identidad Institucional -->
            <div class="app-content-header border-0 pb-0">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="home-hero-header mt-2 d-flex align-items-center">
                                <img src="public/assets/img/logos/VEN 9-1-1.webp" alt="VEN 911 Logo" class="home-hero-logo me-3">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div>
                                            <h1 class="home-hero-title h2 mb-1">Panel de Control</h1>
                                            <p class="home-hero-subtitle fw-medium mb-0" style="color: #475569;">Sistema Integrado de Gestión de Emergencias VEN 911</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Widgets y Estadísticas Dinámicas -->
            <div class="app-content mt-4">
                <div class="container-fluid">

                    <!-- Visualizaciones de Datos: Segmentación por Rol -->
                    <div class="row g-4">
                        <div class="col-12">
                            <?php 
                            $rolId = (int)($_SESSION['user_rol_id'] ?? 0);
                            switch ($rolId) {
                                case 1: require __DIR__ . '/componentes/_stats_admin.php'; break;
                                case 2: require __DIR__ . '/componentes/_stats_operador.php'; break;
                                case 3: require __DIR__ . '/componentes/_stats_despachador.php'; break;
                                case 4: require __DIR__ . '/componentes/_stats_jefatura.php'; break;
                                default: 
                                    echo '<div class="alert alert-info shadow-sm rounded-4">No se han definido visualizaciones para su rol.</div>';
                            }
                            ?>
                        </div>

                        <!-- Widget de Estado del Servidor WebSocket (Solo Administrador) -->
                        <?php if ($rolId === 1): ?>
                        <div class="col-12 mt-4">
                            <?php require __DIR__ . '/componentes/_widget_ws.php'; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </main>

        <!-- Pie de página Institucional -->
        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>
    
    <!-- 4. COMPONENTES DE ACCIÓN (MODALES REUTILIZADOS) -->
    <?php if ($rolId === 2 && tienePerm('fichas', 'crear')): ?>
        <?php require __DIR__ . '/../fichas/componentes/_modal_crear.php'; ?>
    <?php endif; ?>

    <!-- 5. CARGA DE ASSETS JAVASCRIPT -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <!-- Motor de gráficas -->
    <script src="public/libs/apexcharts/apexcharts.min.js"></script>
    
    <script>
        /**
         * Inicialización de estadísticas globales.
         * Se inyectan los datos desde el controlador para que ApexCharts los procese.
         */
        window.VENT911_STATS = <?php echo json_encode($datos ?? []); ?>;
        window.USER_ROL = <?php echo (int)($_SESSION['user_rol_id'] ?? 0); ?>;
        window.VEN911_PERM_EDITAR = <?php echo tienePerm('fichas', 'editar') ? 'true' : 'false'; ?>;
        window.VEN911_PERM_CAMBIAR_ESTADO = <?php echo tienePerm('fichas', 'cambiar_estado') ? 'true' : 'false'; ?>;
    </script>
    
    <!-- Scripts de Lógica Modular -->
    <script src="public/js/home/home_graficas.js"></script>
    
    <?php if ((int)($_SESSION['user_rol_id'] ?? 0) === 1): ?>
    <script src="public/js/home/home_ws.js"></script>
    <?php endif; ?>

    <!-- Lógica de Fichas para el botón de creación rápida -->
    <?php if ($rolId === 2 && tienePerm('fichas', 'crear')): ?>
        <script src="public/libs/datatables/dataTables.min.js"></script>
        <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>
        <script src="public/js/comun/datatables_config.js"></script>
        <script src="public/js/fichas/fichas_datatable.js"></script>
    <?php endif; ?>

</body>
</html>
