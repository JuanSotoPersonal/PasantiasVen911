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

                        <!-- Widget de Estado del Servidor WebSocket (Solo Administrador) -->
                        <?php if ((int)($_SESSION['user_rol_id'] ?? 0) === 1): ?>
                        <div class="col-xl-6 col-lg-4 col-12">
                            <div class="card shadow-sm border-0 rounded-4" id="card-estado-ws">
                                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                    <h3 class="card-title text-success fw-bold mb-0">
                                        <i class="bi bi-broadcast me-2"></i>Estado del Servidor WS
                                    </h3>
                                    <button class="btn btn-sm btn-outline-success rounded-pill" id="btn-refrescar-ws" title="Verificar ahora">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Indicador principal -->
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div id="ws-indicador" style="width:18px;height:18px;border-radius:50%;background:#6c757d;flex-shrink:0;"></div>
                                        <div>
                                            <div class="fw-bold fs-6" id="ws-estado-texto">Verificando...</div>
                                            <small class="text-muted" id="ws-latencia-texto"></small>
                                        </div>
                                    </div>
                                    <!-- Info técnica -->
                                    <div class="small text-muted border-top pt-3">
                                        <div><i class="bi bi-hdd-network me-1"></i>WebSocket: <code>ws://localhost:8080</code></div>
                                        <div><i class="bi bi-diagram-2 me-1"></i>HTTP Receptor: <code>127.0.0.1:8081</code></div>
                                    </div>
                                    <!-- Instrucción de arranque -->
                                    <div class="mt-3 p-2 rounded-3 bg-light small" id="ws-instruccion" style="display:none;">
                                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                        El demonio no está activo. Ejecuta:
                                        <code class="d-block mt-1">iniciar_ws.bat</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </main>

        <!-- Pie de página Institucional -->
        <?php require __DIR__ . '/partials/footer.php'; ?>

    </div>

    <!-- 5. CARGA DE ASSETS JAVASCRIPT -->
    <?php require __DIR__ . '/partials/scripts.php'; ?>

    <!-- Motor de gráficas -->
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

    <?php if ((int)($_SESSION['user_rol_id'] ?? 0) === 1): ?>
    <!-- Widget de Estado del Servidor WebSocket (Solo Admin) -->
    <script>
        (function () {
            'use strict';

            const $indicador  = document.getElementById('ws-indicador');
            const $texto      = document.getElementById('ws-estado-texto');
            const $latencia   = document.getElementById('ws-latencia-texto');
            const $instruccion = document.getElementById('ws-instruccion');
            const $btnRefresh = document.getElementById('btn-refrescar-ws');

            if (!$indicador) return; // Seguridad: solo corre si el widget existe

            function verificarEstadoWS() {
                $texto.textContent = 'Verificando...';
                $indicador.style.background = '#6c757d';
                $latencia.textContent = '';

                fetch('index.php?url=notificacion/estadoServidor')
                    .then(r => r.json())
                    .then(data => {
                        if (data.activo) {
                            $indicador.style.background = '#16a34a'; // Verde
                            $texto.textContent = '✓ Activo — Operativo';
                            $latencia.textContent = `Latencia: ${data.latencia_ms} ms`;
                            if ($instruccion) $instruccion.style.display = 'none';
                        } else {
                            $indicador.style.background = '#dc3545'; // Rojo
                            $texto.textContent = '✗ Inactivo — No detectado';
                            $latencia.textContent = 'Puerto 8081 sin respuesta';
                            if ($instruccion) $instruccion.style.display = 'block';
                        }
                    })
                    .catch(() => {
                        $indicador.style.background = '#ffc107'; // Amarillo
                        $texto.textContent = '⚠ Error de comunicación';
                        $latencia.textContent = '';
                    });
            }

            // Verificar al cargar la página
            verificarEstadoWS();

            // Botón de refresco manual
            if ($btnRefresh) {
                $btnRefresh.addEventListener('click', verificarEstadoWS);
            }

            // Auto-verificación cada 30 segundos
            setInterval(verificarEstadoWS, 30000);
        })();
    </script>
    <?php endif; ?>

</body>
</html>


