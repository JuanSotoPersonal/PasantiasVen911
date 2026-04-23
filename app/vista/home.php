<?php
/**
 * home.php - Vista Principal (Dashboard)
 * 
 * Este archivo renderiza el panel de control central del sistema Ven911.
 * Incluye la distribución visual de estadísticas y el acceso a los módulos core.
 * Basado en el estándar de diseño AdminLTE 3 configurado para la institución.
 */

// 1. CONFIGURACIÓN DE PÁGINA
$pageName = 'home';
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Dashboard Central</title>
    
    <!-- 2. CABECERA GLOBALES Y ESTILOS -->
    <?php require __DIR__ . '/partials/head.php'; ?>
    
    <!-- Estilos específicos para notificaciones push y diseño home -->
    <link rel="stylesheet" href="public/css/notificaciones.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    
    <div class="app-wrapper">

        <!-- 3. COMPONENTES DE INTERFAZ GLOBAL -->
        <?php require __DIR__ . '/partials/navbar.php'; ?>
        <?php require __DIR__ . '/partials/sidebar.php'; ?>

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
                                    <h1 class="home-hero-title h2 mb-1">Panel de Control</h1>
                                    <p class="home-hero-subtitle text-muted mb-0">Sistema Integrado de Gestión de Emergencias VEN 911</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Widgets y Estadísticas Dinámicas -->
            <div class="app-content mt-4">
                <div class="container-fluid">
                    
                    <!-- Bloque de Bienvenida: UI Informativa -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-body p-4 bg-white border-start border-success border-5">
                                    <h3 class="fw-bold text-success mb-2">¡Bienvenido al Centro de Mando!</h3>
                                    <p class="text-secondary mb-0">
                                        Estado del sistema: <span class="badge bg-success-subtle text-success">Óptimo</span>. 
                                        Utilice el menú lateral para gestionar fichas de emergencia y monitorizar el despacho en tiempo real.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visualizaciones de Datos: ApexCharts -->
                    <div class="row g-4">
                        <div class="col-xl-6 col-lg-8 col-12">
                            <div class="card h-100 shadow-sm border-0 rounded-4">
                                <div class="card-header bg-white border-bottom p-3">
                                    <h3 class="card-title text-success fw-bold mb-0">
                                        <i class="bi bi-pie-chart-fill me-2 text-warning"></i>Distribución de Personal por Rol
                                    </h3>
                                </div>
                                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                                    <!-- Contenedor donde se dibuja la gráfica vía JS -->
                                    <div id="usuariosChart" style="width: 100%; min-height: 350px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </main>

        <!-- Pie de página Institucional -->
        <?php require __DIR__ . '/partials/footer.php'; ?>

    </div>

    <!-- 5. CARGA DE ASSETS JAVASCRIPT -->
    <?php require __DIR__ . '/partials/scripts.php'; ?>

    <!-- Módulos de soporte: Notificaciones SSE y motor de gráficas -->
    <script src="public/js/comun/notificaciones.js"></script>
    <script src="public/libs/apexcharts/apexcharts.min.js"></script>
    
    <script>
        /**
         * Inicialización de estadísticas globales.
         * Se inyectan los datos desde el controlador para que ApexCharts los procese.
         */
        window.VENT911_STATS = <?php echo json_encode($datos ?? []); ?>;
    </script>
    
    <!-- Script core del home: Renderizado de componentes visuales -->
    <script src="public/js/home/home_graficas.js"></script>

</body>
</html>


