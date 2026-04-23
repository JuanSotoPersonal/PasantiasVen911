<?php
/**
 * scripts.php - Cargador Central de Lógica Javascript
 * 
 * Centraliza la invocación de librerías core y configuraciones de UI
 * condicionales según el módulo activo.
 */

// 1. DETERMINACIÓN DEL CONTEXTO DE EJECUCIÓN
$pageName = $pageName ?? 'home';
?>

<!-- 2. LIBRERÍAS TRANSVERSALES (SweetAlert2) -->
<script src="public/libs/sweetalert2/sweetalert2.min.js"></script>

<!-- 3. CARGA SEGÚN CONTEXTO (Login vs Dashboard) -->
<?php if (in_array($pageName, ['login', 'setup'])): ?>
    <!-- Scripts exclusivos de Autenticación -->
    <?php if ($pageName === 'login'): ?>
        <script src="public/js/auth/login.js"></script>
    <?php elseif ($pageName === 'setup'): ?>
        <script src="public/js/auth/setup.js"></script>
    <?php endif; ?>

<?php else: ?>
    <!-- Scripts Robustos del Sistema (Dashboard) -->
    <script src="public/libs/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>
    <script src="public/libs/popperjs/popper.min.js"></script>
    <script src="public/libs/bootstrap/bootstrap.min.js"></script>
    <script src="public/js/adminlte.js"></script>
    
    <!-- jQuery Ecosystem (DataTables/Select2 dependencias) -->
    <script src="public/libs/datatables/jquery-3.7.1.min.js"></script>
    <script src="public/libs/select2/select2.min.js"></script>
    <script src="public/libs/select2/es.js"></script>

    <!-- 4. CONFIGURACIÓN DE COMPORTAMIENTO DE INTERFAZ -->
    <script>
        /**
         * Inicialización de OverlayScrollbars para la Sidebar.
         * Garantiza una experiencia de scroll suave y estética.
         */
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-scroll-area';
        const DefaultConfig = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };

        document.addEventListener('DOMContentLoaded', function () {
            const initScroll = () => {
                const target = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
                if (target && typeof OverlayScrollbarsGlobal !== 'undefined') {
                    OverlayScrollbarsGlobal.OverlayScrollbars(target, {
                        overflow: { x: 'hidden' },
                        scrollbars: {
                            theme: DefaultConfig.scrollbarTheme,
                            autoHide: DefaultConfig.scrollbarAutoHide,
                            clickScroll: DefaultConfig.scrollbarClickScroll,
                        },
                    });
                } else if (target) {
                    setTimeout(initScroll, 100);
                }
            };
            
            // Solo activar scrollbars en desktop para optimizar rendimiento móvil
            const isMobile = window.innerWidth <= 992;
            if (!isMobile) initScroll();
        });
    </script>
<?php endif; ?>

