<?php
/**
 * sidebar.php - Menú de Navegación Lateral (Estructura Orbital)
 * 
 * Gestiona la jerarquía de acceso a los módulos del sistema según el RBAC.
 * Incluye lógica de persistencia visual (Active State) y branding institucional.
 */

// DETERMINACIÓN DE CONTEXTO ACTUAL (Ruteo activo)
$urlActual = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$seccion   = explode('/', $urlActual)[0] ?? '';
?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    
    <!-- 1. BLOQUE DE IDENTIDAD (BRANDING) -->
    <div class="sidebar-brand">
        <a href="index.php?url=home" class="brand-link">
            <span class="brand-text">
                <span class="brand-text-main">VEN 911</span>
                <span class="brand-text-sub">Carabobo</span>
            </span>
        </a>
    </div>

    <!-- 2. NAVEGACIÓN CENTRAL Y MÓDULOS OPERATIVOS -->
    <div class="sidebar-wrapper">
        <div class="sidebar-scroll-area">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
                    
                    <!-- Dashboard: Nodo Raíz -->
                    <li class="nav-item">
                        <a href="index.php?url=home" class="nav-link <?= $seccion === 'home' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-speedometer"></i>
                            <p>Inicio</p>
                        </a>
                    </li>

                    <!-- Módulo: Reportes e Inteligencia -->
                    <?php if (tienePerm('reportes', 'ver')): ?>
                        <li class="nav-item">
                            <a href="index.php?url=reporte" class="nav-link <?= $seccion === 'reporte' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                                <p>Reportes</p>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Módulo: Fichas de Emergencia -->
                    <?php if (tienePerm('fichas', 'ver')): ?>
                        <?php $tabFicha = $_GET['t'] ?? 'todas'; ?>
                        <li class="nav-item <?= $seccion === 'ficha' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $seccion === 'ficha' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-file-earmark-medical-fill"></i>
                                <p>Fichas de Emergencia<i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="index.php?url=ficha&t=todas" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'todas') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'ficha' && $tabFicha === 'todas') ? 'bi-collection-fill' : 'bi-collection' ?>"></i>
                                        <p>Todas las Fichas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=ficha&t=pendientes" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'pendientes') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-hourglass-split"></i>
                                        <p>Pendientes</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=ficha&t=en_proceso" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'en_proceso') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-arrow-repeat"></i>
                                        <p>En Proceso</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=ficha&t=atendidos" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'atendidos') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-check-lg text-success"></i>
                                        <p>Atendidos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=ficha&t=cerradas" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'cerradas') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-lock-fill"></i>
                                        <p>Cerradas</p>
                                    </a>
                                </li>
                                <?php if (tienePerm('configuracion', 'gestionar')): ?>

                                    <li class="nav-item">
                                        <a href="index.php?url=ficha&t=configuracion" class="nav-link <?= ($seccion === 'ficha' && $tabFicha === 'configuracion') ? 'active' : '' ?>">
                                            <i class="nav-icon bi bi-gear-fill"></i>
                                            <p>Configuración</p>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Módulo: Despacho y Operativa -->
                    <?php if (tienePerm('despachos', 'ver')): ?>
                        <?php $tabDespacho = $_GET['t'] ?? 'general'; ?>
                        <li class="nav-item <?= $seccion === 'despacho' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $seccion === 'despacho' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-broadcast"></i>
                                <p>Centro de Despacho<i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="index.php?url=despacho&t=general" class="nav-link <?= ($seccion === 'despacho' && $tabDespacho === 'general') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'despacho' && $tabDespacho === 'general') ? 'bi-globe2' : 'bi-globe2' ?>"></i>
                                        <p>Cola General</p>
                                    </a>
                                </li>
                                <?php if ((int)$_SESSION['user_rol_id'] !== 4): ?>
                                <li class="nav-item">
                                    <a href="index.php?url=despacho&t=propias" class="nav-link <?= ($seccion === 'despacho' && $tabDespacho === 'propias') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'despacho' && $tabDespacho === 'propias') ? 'bi-person-check-fill' : 'bi-person-check' ?>"></i>
                                        <p>Mis Fichas</p>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Módulo: Buzón de Notificaciones -->
                    <?php if (tienePerm('fichas', 'ver')): ?>
                        <li class="nav-item">
                            <a href="index.php?url=notificacion" class="nav-link <?= $seccion === 'notificacion' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-bell-fill"></i>
                                <p>Notificaciones</p>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Módulo: Gestión de Personal -->
                    <?php if (tienePerm('usuarios', 'ver')): ?>
                        <?php $tabUsr = $_GET['t'] ?? 'todos'; ?>
                        <li class="nav-item <?= $seccion === 'usuario' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $seccion === 'usuario' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-people-fill"></i>
                                <p>Gestión de Usuarios<i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="index.php?url=usuario&t=todos" class="nav-link <?= ($seccion === 'usuario' && $tabUsr === 'todos') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'usuario' && $tabUsr === 'todos') ? 'bi-people-fill' : 'bi-people' ?>"></i>
                                        <p>Todos los Usuarios</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=usuario&t=rol_1" class="nav-link <?= ($seccion === 'usuario' && $tabUsr === 'rol_1') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'usuario' && $tabUsr === 'rol_1') ? 'bi-shield-fill' : 'bi-shield' ?>"></i>
                                        <p>Administradores</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=usuario&t=rol_2" class="nav-link <?= ($seccion === 'usuario' && $tabUsr === 'rol_2') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'usuario' && $tabUsr === 'rol_2') ? 'bi-headset' : 'bi-headset' ?>"></i>
                                        <p>Operadores</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=usuario&t=rol_3" class="nav-link <?= ($seccion === 'usuario' && $tabUsr === 'rol_3') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'usuario' && $tabUsr === 'rol_3') ? 'bi-broadcast' : 'bi-broadcast' ?>"></i>
                                        <p>Despachadores</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=usuario&t=rol_4" class="nav-link <?= ($seccion === 'usuario' && $tabUsr === 'rol_4') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'usuario' && $tabUsr === 'rol_4') ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                        <p>Jefatura</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?url=usuario&t=inactivos" class="nav-link <?= ($seccion === 'usuario' && $tabUsr === 'inactivos') ? 'active' : '' ?>">
                                        <i class="nav-icon bi <?= ($seccion === 'usuario' && $tabUsr === 'inactivos') ? 'bi-person-x-fill text-danger' : 'bi-person-x text-danger' ?>"></i>
                                        <p>Cuentas Inactivas</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Módulo: Auditoría y Cumplimiento -->
                    <?php if (tienePerm('historial', 'ver')): ?>
                        <?php $tabLog = $_GET['t'] ?? 'sistema'; ?>
                        <li class="nav-item <?= $seccion === 'evento' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $seccion === 'evento' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-shield-check"></i>
                                <p>Auditoría Integral<i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php if ((int)$_SESSION['user_rol_id'] !== 4): ?>
                                <li class="nav-item">
                                    <a href="index.php?url=evento&t=sistema" class="nav-link <?= ($seccion === 'evento' && ($tabLog === 'sistema' || empty($tabLog))) ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-activity"></i>
                                        <p>Logs del Sistema</p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a href="index.php?url=evento&t=ficha" class="nav-link <?= ($seccion === 'evento' && $tabLog === 'ficha') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-clock-fill"></i>
                                        <p>Trazabilidad de Fichas</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
        </div>

        <!-- 3. BLOQUE FIJO: AUXILIARES Y SOPORTE -->
        <div class="sidebar-sticky-footer">
            <div class="sidebar-footer-nav">
                <ul class="nav sidebar-menu flex-column">
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-info-circle"></i>
                            <p>Preguntas Frecuentes</p>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 4. FOOTER INSTITUCIONAL (BRANDING ESTATAL) -->
            <div class="sidebar-institutional-logo">
                <img src="public/assets/img/logos/LOGO MIJP JUSTICIA Y PAZ - BLANCO (1).webp" alt="MIJP" class="sidebar-mijp-logo">
                <div class="footer-logo-divider"></div>
                <img src="public/assets/img/logos/VEN 9-1-1.webp" alt="VEN 9-1-1" class="sidebar-footer-ven-logo">
            </div>
        </div>

    </div>
</aside>

