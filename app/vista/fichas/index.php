<?php
/**
 * index.php - Módulo de Gestión de Fichas de Emergencia
 * 
 * Punto central del sistema de despacho. Permite la recepción, trazabilidad
 * y cierre de incidencias, además de la configuración de parámetros core.
 */

// 1. CONFIGURACIÓN DE ESTADO Y FILTRADO
$pageName  = 'fichas';
$tabActiva = $_GET['t'] ?? 'todas';

// Determinación del estado para filtrado en BD
$estadoFiltro = match($tabActiva) {
    'pendientes'  => 'Pendiente',
    'en_proceso'  => 'En Proceso',
    'atendidos'   => 'Atendido',
    'cerradas'    => 'Cerrado',
    default       => 'todos',
};
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Fichas de Emergencia</title>
    
    <!-- 2. CABECERA GLOBAL Y RECURSOS ESPECÍFICOS -->
    <?php require __DIR__ . '/../partials/head.php'; ?>
    
    <link rel="stylesheet" href="public/css/fichas.css?v=1.2" />
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <div class="app-wrapper">

        <!-- 3. COMPONENTES GLOBALES DE INTERFAZ -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main">
            
            <!-- Encabezado de Sección: Identidad del Módulo -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0 fw-bold">
                                <?= ($tabActiva === 'configuracion') ? 'Configuración del Sistema' : "Fichas: {$estadoFiltro}" ?>
                            </h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="index.php?url=home">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Gestión de Fichas</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. CONTENIDO DINÁMICO (TABLAS O CONFIGURACIÓN) -->
            <div class="app-content">
                <div class="container-fluid">
                    <?php
                    if ($tabActiva === 'configuracion' && tienePerm('configuracion', 'gestionar')) {
                        require __DIR__ . '/componentes/_configuracion.php';
                    } else {
                        require __DIR__ . '/componentes/_tabla_fichas.php';
                    }
                    ?>
                </div>
            </div>

        </main>

        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

    <!-- 5. CAPA DE SEGURIDAD Y ACCIONES (MODALES) -->
    <?php if ($tabActiva !== 'configuracion'): ?>
        <?php if (tienePerm('fichas', 'crear')): ?>
            <?php require __DIR__ . '/componentes/_modal_crear.php'; ?>
        <?php endif; ?>
        
        <?php if (tienePerm('fichas', 'editar')): ?>
            <?php require __DIR__ . '/componentes/_modal_editar.php'; ?>
        <?php endif; ?>
        
        <?php require __DIR__ . '/componentes/_modal_detalle.php'; ?>
    <?php endif; ?>


    <!-- 6. CARGA DE MOTOR JAVASCRIPT Y METADATOS -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <!-- Librerías de datos (DataTables) -->
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Transmisión de metadatos de sesión al contexto JS -->
    <script>
        window.VEN911_PERM_EDITAR         = <?= tienePerm('fichas', 'editar')         ? 'true' : 'false' ?>;
        window.VEN911_PERM_CAMBIAR_ESTADO  = <?= tienePerm('fichas', 'cambiar_estado')  ? 'true' : 'false' ?>;
    </script>

    <!-- Carga condicional de lógica de negocio -->
    <script src="public/js/comun/datatables_config.js"></script>
    <script src="public/js/comun/fichas_comun.js"></script>
    <?php if ($tabActiva === 'configuracion'): ?>
        <script src="public/js/fichas/fichas_configuracion.js"></script>
    <?php else: ?>
        <script src="public/js/fichas/fichas_datatable.js?v=1.0"></script>
    <?php endif; ?>

</body>
</html>

