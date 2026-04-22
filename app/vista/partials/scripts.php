<?php
/**
 * Partial: Scripts globales
 * Centraliza la carga de librerías JS y configuraciones comunes de la UI.
 * Utiliza la variable $pageName para carga condicional.
 */
$pageName = $pageName ?? 'home';
?>

<!-- SweetAlert2 (Común a todas las vistas) -->
<script src="public/libs/sweetalert2/sweetalert2.min.js"></script>

<?php if ($pageName === 'login' || $pageName === 'setup'): ?>
    <!-- Scripts exclusivos de Autenticación y Configuración Inicial -->
    <?php if ($pageName === 'login'): ?>
        <script src="public/js/auth/login.js"></script>
    <?php elseif ($pageName === 'setup'): ?>
        <script src="public/js/auth/setup.js"></script>
    <?php endif; ?>

<?php else: ?>
    <!-- Scripts exclusivos del Dashboard / Sistema -->
    <script src="public/libs/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>
    <script src="public/libs/popperjs/popper.min.js"></script>
    <script src="public/libs/bootstrap/bootstrap.min.js"></script>
    <script src="public/js/adminlte.js"></script>

    <!-- Configuración Global de Scrollbars (OverlayScrollbars) -->
    <script>
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
                        overflow: {
                            x: 'hidden',
                        },
                        scrollbars: {
                            theme: DefaultConfig.scrollbarTheme,
                            autoHide: DefaultConfig.scrollbarAutoHide,
                            clickScroll: DefaultConfig.scrollbarClickScroll,
                        },
                    });
                } else if (target) {
                    // Re-intento si la librería no ha cargado aún
                    setTimeout(initScroll, 100);
                }
            };
            
            const isMobile = window.innerWidth <= 992;
            if (!isMobile) initScroll();
        });
    </script>
<?php endif; ?>
