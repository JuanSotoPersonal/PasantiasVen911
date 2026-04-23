<?php
/**
 * index.php - Módulo de Gestión de Usuarios
 * 
 * Interfaz administrativa para el control de acceso, roles y perfiles del personal.
 * Implementa un sistema dinámico de tablas (DataTables) filtradas por rol y estado.
 */

// 1. CONFIGURACIÓN DE NAVEGACIÓN
$pageName  = 'usuarios';
$tabActiva = $_GET['t'] ?? 'todos';
?>
<!doctype html>
<html lang="es">

<head>
    <title>Ven911 | Gestión de Usuarios</title>
    
    <!-- 2. CABECERA GLOBAL Y ASSETS -->
    <?php require __DIR__ . '/../partials/head.php'; ?>
    
    <link rel="stylesheet" href="public/css/usuarios.css" />
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <div class="app-wrapper">

        <!-- 3. COMPONENTES GLOBALES DE INTERFAZ -->
        <?php require __DIR__ . '/../partials/navbar.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main">
            
            <!-- Encabezado de Sección: Breadcrumbs e Identidad -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0 fw-bold">Personal y Operadores</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="index.php?url=home">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Gestión de Usuarios</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. CONTENIDO DINÁMICO (SISTEMA DE TABLAS) -->
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <?php
                            // Ruteo interno de vistas de dependencia
                            $vistaCargar = match(true) {
                                $tabActiva === 'todos'       => '_tabla_principal.php',
                                $tabActiva === 'inactivos'   => '_tabla_inactivos.php',
                                strpos($tabActiva, 'rol_') === 0 => '_tablas_roles.php',
                                default                       => '_tabla_principal.php'
                            };

                            // Inyección de la tabla seleccionada
                            require __DIR__ . "/componentes/{$vistaCargar}";
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

    <!-- 5. CAPA DE SEGURIDAD VISUAL (MODALES) -->
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


    <!-- 6. CARGA DE MOTOR JAVASCRIPT Y PERMISOS -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>

    <!-- Librerías de datos (DataTables) -->
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Transmisión de metadatos de sesión al contexto JS -->
    <script>
        window.VEN911_PERM_CREAR  = <?= tienePerm('usuarios', 'crear')  ? 'true' : 'false' ?>;
        window.VEN911_PERM_EDITAR = <?= tienePerm('usuarios', 'editar') ? 'true' : 'false' ?>;
        window.USER_ROL_ID        = <?= (int)$_SESSION['user_rol_id'] ?>;
    </script>

    <!-- Lógica de comportamiento del módulo -->
    <script src="public/js/comun/datatables_config.js"></script>
    <script src="public/js/usuarios/usuarios_datatable.js"></script>
    <script src="public/js/usuarios/usuarios_roles.js"></script>

</body>
</html>

