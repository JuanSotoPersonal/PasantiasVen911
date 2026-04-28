<?php
/**
 * _modal_editar.php — Componente: Modal de Edición de Despacho
 * 
 * Permite actualizar los datos de campo de un despacho existente
 * (unidad designada, mando a cargo, persona que atiende).
 * La ficha y el organismo son de solo lectura, no se permiten cambiar.
 */
?>

<div class="modal fade" id="modalEditarDespacho" tabindex="-1" aria-labelledby="modalEditarDespachoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <!-- Encabezado institucional -->
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white" id="modalEditarDespachoLabel">
                    <i class="bi bi-pencil-square me-2"></i>Editar Despacho <span id="editarDespachoIdLabel" class="ms-1 opacity-75"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Formulario de edición -->
            <div class="modal-body p-4">
                <form id="formEditarDespacho" novalidate>
                    <input type="hidden" id="editar_despacho_id" name="despacho_id">

                    <!-- Datos de contexto (solo lectura) -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-info-circle-fill me-2"></i>1. Contexto del Despacho
                        </h6>
                    </div>

                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary mb-1">Ficha #</label>
                            <p id="editar_ctx_ficha_id" class="fw-bold text-success mb-0"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary mb-1">Organismo</label>
                            <p id="editar_ctx_organismo" class="fw-semibold mb-0"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary mb-1">Estatus Actual</label>
                            <div id="editar_ctx_estatus" class="mt-1"></div>
                        </div>
                    </div>

                    <!-- Datos editables -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-pencil-fill me-2"></i>2. Datos a Actualizar
                        </h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar_unidad_designada" class="form-label fw-semibold small text-secondary">Unidad Designada <span class="text-danger">*</span></label>
                            <input type="text" id="editar_unidad_designada" name="unidad_designada"
                                   class="form-control shadow-sm border-2" placeholder="Ej: Patrulla 03-B">
                        </div>

                        <div class="col-md-6">
                            <label for="editar_mando_acargo" class="form-label fw-semibold small text-secondary">Mando a Cargo <span class="text-danger">*</span></label>
                            <input type="text" id="editar_mando_acargo" name="mando_acargo"
                                   class="form-control shadow-sm border-2" placeholder="Ej: Inspector Rodríguez">
                        </div>

                        <div class="col-md-12">
                            <label for="editar_persona_atiende" class="form-label fw-semibold small text-secondary">Persona que Atiende (Opcional)</label>
                            <input type="text" id="editar_persona_atiende" name="persona_atiende"
                                   class="form-control shadow-sm border-2" placeholder="Ej: Funcionario López">
                        </div>
                    </div>

                </form>
            </div>

            <!-- Pie del modal -->
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">
                    Descartar
                </button>
                <button type="button" id="btnGuardarEdicionDespacho" class="btn btn-ven-primary px-4 shadow-sm">
                    <i class="bi bi-save-fill me-2"></i>Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>

        </div>
    </div>
</div>
