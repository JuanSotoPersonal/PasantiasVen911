<?php
/**
 * index.php - Módulo de Historial y Auditoría de Sistema
 * 
 * Este módulo gestiona el rastro de actividades (logs) de los usuarios y las fichas.
 * Permite visualizar quién, cuándo y qué acción se realizó en la plataforma, 
 * cumpliendo con los estándares de seguridad y trazabilidad.
 */

// 1. CONFIGURACIÓN DE CONTEXTO
$pageName  = 'eventos';
$tabActiva = $tabActiva ?? ($_GET['t'] ?? 'sistema');
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Historial de Sistema</title>
    
    <!-- 2. RECURSOS DE CABECERA (CSS GLOBAL) -->
    <?php require __DIR__ . '/../partials/head.php'; ?>
    
    <link rel="stylesheet" href="public/css/usuarios.css" />
    <link rel="stylesheet" href="public/css/eventos.css" />
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <!-- 3. CONTENEDOR PRINCIPAL -->
    <div class="app-wrapper">

        <!-- Navegación y Menú Lateral -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main">
            
            <!-- Encabezado de Sección (Breadcrumbs) -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0 fw-bold">Historial de Sistema</h3>
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

            <!-- 4. CONTENIDO DINÁMICO (TABLAS DE AUDITORÍA) -->
            <div class="app-content">
                <div class="container-fluid">
                    <?php
                    // Carga condicional según el tipo de registro seleccionado (Fichas o Sistema)
                    if ($tabActiva === 'ficha') {
                        require __DIR__ . '/componentes/_tabla_fichas.php';
                    } else {
                        require __DIR__ . '/componentes/_tabla_sistema.php';
                    }
                    ?>
                </div>
            </div>

        </main>

        <!-- Pie de Página Institucional -->
        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

    <!-- 5. CAPA DE DETALLES (MODALES) -->
    <?php require __DIR__ . '/componentes/_modal_detalles.php'; ?>


    <!-- 6. MOTOR JAVASCRIPT Y LÓGICA DE DATOS -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <!-- Librerías de DataTables Core -->
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Configuración e Inicialización de Tablas -->
    <script src="public/js/comun/datatables_config.js"></script>
    
    <?php if ($tabActiva === 'ficha'): ?>
        <script src="public/js/eventos/eventos_fichas_datatable.js?v=1.0"></script>
    <?php else: ?>
        <script src="public/js/eventos/eventos_datatable.js?v=1.1"></script>
    <?php endif; ?>

</body>
</html>
