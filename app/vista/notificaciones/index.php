<?php
/**
 * VISTA: Buzón de Notificaciones
 * Propósito: Visualizar el historial completo de alertas del usuario mediante DataTables.
 */
$tituloPagina = "Buzón de Notificaciones";
$seccion = 'notificacion';
require_once 'app/vista/partials/header.php';
require_once 'app/vista/partials/navbar.php';
require_once 'app/vista/partials/sidebar.php';
?>

<!-- Estilos Específicos -->
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

<div class="content-wrapper">
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
</div>

<?php 
require_once 'app/vista/partials/footer.php';
require_once 'app/vista/partials/scripts.php'; 
?>
<!-- Script principal de DataTables para el Buzón -->
<script src="public/js/notificaciones/index.js?v=<?= time() ?>"></script>
</body>
</html>
