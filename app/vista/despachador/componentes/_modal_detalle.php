<?php
/**
 * _modal_detalle.php — Componente: Modal de Gestión de Despachos por Ficha
 *
 * Panel de operaciones para una ficha específica. Muestra:
 * 1. Resumen completo de la ficha + botones de cambio de estado (solo Despachador/Admin)
 * 2. Botón de edición rápida de ficha (abre _modal_editar_ficha.php)
 * 3. Lista de organismos despachados con su estatus y botones de avance
 * 4. Botón para asignar un organismo adicional
 *
 * Contenido cargado dinámicamente vía AJAX al abrir el modal.
 */
?>

<div class="modal fade" id="modalDetalleDespacho" tabindex="-1"
     aria-labelledby="modalDetalleDespachoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <!-- Encabezado institucional -->
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white" id="modalDetalleDespachoLabel">
                    <i class="bi bi-file-earmark-medical-fill me-2"></i>
                    Gestión de Ficha <span id="detalleDespachoIdLabel" class="ms-1 opacity-75"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                <!-- === BLOQUE 1: RESUMEN DE LA FICHA === -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="section-title-container">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2 pe-3 mb-0">
                            <i class="bi bi-file-earmark-medical me-2"></i>Datos de la Ficha
                        </h6>
                    </div>

                    <!-- Botón de edición: solo para Despachador y Admin -->
                    <?php if (tienePerm('fichas', 'editar')): ?>
                        <button type="button" id="btnEditarFichaDesdeDetalle"
                                class="btn btn-ven-edit btn-sm px-3 shadow-sm rounded-pill d-none"
                                data-ficha-id="">
                            <i class="bi bi-pencil-fill me-1"></i> Editar Ficha
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Resumen de la ficha (cargado por AJAX) -->
                <div id="contenidoResumenFicha">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status"></div>
                    </div>
                </div>

                <!-- === BLOQUE 2: CONTROL MANUAL DE ESTADO DE FICHA === -->
                <?php if (tienePerm('fichas', 'cambiar_estado') || tienePerm('despachos', 'cambiar_estado')): ?>
                    <div id="seccionCambioEstadoFicha" class="d-none mt-3 rounded-3 border overflow-hidden"
                         style="border-color: #bbf7d0 !important;">

                        <!-- Sub-cabecera del panel de control de estado -->
                        <div class="px-3 py-2 d-flex justify-content-between align-items-center"
                             style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0;">
                            <span class="fw-bold small text-success">
                                <i class="bi bi-sliders me-1"></i>Control de Estado de la Ficha
                            </span>
                            <span class="text-muted" style="font-size:0.72rem;">
                                Solo Despachador / Administrador
                            </span>
                        </div>

                        <!-- Stepper visual de progresión (renderizado por JS) -->
                        <div class="px-3 pt-3 pb-1" id="contenedorStepperFicha"></div>

                        <!-- Botones de avance de estado y selector manual (renderizado por JS) -->
                        <div class="px-3 pb-3" id="contenedorBotonesEstadoFicha"></div>

                    </div>
                <?php endif; ?>


                <hr class="my-4" style="border-color: #bbf7d0;">

                <!-- === BLOQUE 3: ORGANISMOS DESPACHADOS === -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="section-title-container">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2 pe-3 mb-0">
                            <i class="bi bi-broadcast me-2"></i>Organismos Despachados
                        </h6>
                    </div>

                    <?php if (tienePerm('despachos', 'crear')): ?>
                        <button type="button" id="btnAgregarOrganismoDesdeDetalle"
                                class="btn btn-ven-primary btn-sm px-3 shadow-sm rounded-pill d-none"
                                data-ficha-id="">
                            <i class="bi bi-plus-circle-fill me-1"></i> Asignar Organismo
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Lista de despachos asignados (cargado por AJAX) -->
                <div id="contenidoListaDespachos">
                    <div class="text-center py-3">
                        <div class="spinner-border text-success" role="status"></div>
                    </div>
                </div>

            </div>

            <!-- Pie del modal -->
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
