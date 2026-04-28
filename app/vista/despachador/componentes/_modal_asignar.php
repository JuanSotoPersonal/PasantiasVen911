<?php
/**
 * _modal_asignar.php — Componente: Modal de Asignación de Organismo
 *
 * Formulario para asignar un organismo de respuesta a una ficha
 * que ya está en estado "En Proceso". El ficha_id se recibe mediante
 * el atributo data-ficha-id del botón que abre este modal.
 * No presenta selector de ficha: la ficha ya está determinada por contexto.
 */
?>

<div class="modal fade" id="modalAsignarDespacho" tabindex="-1"
     aria-labelledby="modalAsignarDespachoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <!-- Encabezado institucional -->
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white" id="modalAsignarDespachoLabel">
                    <i class="bi bi-broadcast me-2"></i>Asignar Organismo de Respuesta
                    <small id="asignarFichaContextLabel" class="ms-2 opacity-75" style="font-size: 0.85rem;"></small>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formAsignarDespacho" novalidate>

                    <!-- Campo oculto: ficha_id inyectado por el botón que abre el modal -->
                    <input type="hidden" id="asignar_ficha_id" name="ficha_id">

                    <!-- Sección 1: Selector de organismo -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-building-fill me-2"></i>1. Organismo de Respuesta
                        </h6>
                    </div>

                    <div class="mb-4">
                        <label for="asignar_organismo_id" class="form-label fw-semibold small text-secondary">
                            Organismo <span class="text-danger">*</span>
                        </label>
                        <select id="asignar_organismo_id" name="organismo_id" class="form-select shadow-sm border-2">
                            <option value="">-- Seleccione organismo --</option>
                        </select>
                    </div>

                    <!-- Sección 2: Datos de campo -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-person-badge-fill me-2"></i>2. Datos de Campo
                        </h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="asignar_unidad_designada" class="form-label fw-semibold small text-secondary">
                                Unidad Designada <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="asignar_unidad_designada" name="unidad_designada"
                                   class="form-control shadow-sm border-2" placeholder="Ej: Patrulla 03-B">
                        </div>

                        <div class="col-md-6">
                            <label for="asignar_mando_acargo" class="form-label fw-semibold small text-secondary">
                                Mando a Cargo <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="asignar_mando_acargo" name="mando_acargo"
                                   class="form-control shadow-sm border-2" placeholder="Ej: Inspector Rodríguez">
                        </div>

                        <div class="col-md-12">
                            <label for="asignar_persona_atiende" class="form-label fw-semibold small text-secondary">
                                Persona que Atiende <span class="text-muted">(Opcional)</span>
                            </label>
                            <input type="text" id="asignar_persona_atiende" name="persona_atiende"
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
                <button type="button" id="btnGuardarDespacho" class="btn btn-ven-primary px-4 shadow-sm">
                    <i class="bi bi-save-fill me-2"></i>Confirmar Despacho
                </button>
            </div>

        </div>
    </div>
</div>
