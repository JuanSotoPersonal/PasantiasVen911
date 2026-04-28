<?php
/**
 * _tabla_despachos.php - Componente: Tablas del Centro de Despacho
 */
?>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">

    <!-- Cabecera del Panel -->
    <div class="card-header bg-white py-3 border-bottom-0">
        <div class="d-flex justify-content-between align-items-center px-1">
            <h3 class="card-title mb-0 fw-bold text-success ps-2">
                <i class="bi bi-broadcast me-2 text-success"></i>
                <?php
                echo match($tabActiva) {
                    'general' => 'Cola General',
                    'propias' => 'Mis Fichas',
                    default   => 'Centro de Despacho'
                };
                ?>
            </h3>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold me-2" style="font-size:0.78rem;">
                <i class="bi bi-activity me-1"></i> Fichas Activas
            </span>
        </div>
    </div>

    <!-- Contenido -->
    <div class="card-body p-0">

        <?php if ($tabActiva === 'general'): ?>
            <!-- TAB 1: Cola General (global, todos los turnos) -->
            <div class="px-4 pt-3 pb-1 d-flex gap-3 flex-wrap">
                <span class="badge-ficha-estado badge-pendiente">
                    <i class="bi bi-hourglass-split"></i> Pendiente — disponible para tomar
                </span>
                <span class="badge-ficha-estado badge-en-proceso">
                    <i class="bi bi-arrow-repeat"></i> En Proceso — con despachador asignado
                </span>
            </div>
            <div class="p-0">
                <div class="table-responsive">
                    <table id="tablaDespachos"
                           class="table table-bordered table-striped table-hover align-middle w-100">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Ficha / Solicitante</th>
                                <th>Tipo de Caso</th>
                                <th>Ubicación</th>
                                <th style="width:130px">Estado</th>
                                <th>Responsable</th>
                                <th>Organismos</th>
                                <th>Apertura</th>
                                <th class="text-center pe-3" style="width:130px">Gestión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Server-Side Processing -->
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tabActiva === 'propias'): ?>
            <!-- TAB 2: Mis Fichas (filtradas por id_owner = usuario actual) -->
            <div class="px-4 pt-3 pb-1 d-flex gap-3 flex-wrap align-items-center">
                <span class="badge-ficha-estado badge-en-proceso">
                    <i class="bi bi-person-check-fill"></i> Fichas que tomaste en este turno
                </span>
                <span class="text-muted small ms-auto">
                    <i class="bi bi-info-circle me-1"></i>
                    Solo muestra fichas activas donde eres el responsable actual.
                </span>
            </div>
            <div class="p-0">
                <div class="table-responsive">
                    <table id="tablaDespachosPropias"
                           class="table table-bordered table-striped table-hover align-middle w-100">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Ficha / Solicitante</th>
                                <th>Tipo de Caso</th>
                                <th>Ubicación</th>
                                <th style="width:130px">Estado</th>
                                <th>Responsable</th>
                                <th>Organismos</th>
                                <th>Apertura</th>
                                <th class="text-center pe-3" style="width:130px">Gestión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Server-Side Processing -->
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>
