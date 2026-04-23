<?php
/**
 * _modal_editar.php - Formulario de Rectificación de Emergencias
 * 
 * Interfaz modal para la modificación de datos en fichas activas (Pendientes/Proceso).
 * Mantiene la misma estructura táctica que el registro para consistencia operativa.
 */
?>

<div class="modal fade" id="modalEditarFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <!-- 1. BLOQUE: CABECERA DE EDICIÓN -->
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white">
                    <i class="bi bi-pencil-square me-2"></i>Actualización de Ficha de Emergencia <span id="editarFichaIdLabel" class="badge bg-white text-success">#</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formEditarFicha" novalidate>
                    <input type="hidden" name="ficha_id" id="editar_ficha_id">

                    <!-- 2. BLOQUE: IDENTIFICACIÓN DEL SOLICITANTE -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-person-badge-fill me-2"></i>1. Identificación del Solicitante
                        </h6>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="editar_cedula_solicitante" class="form-label fw-semibold small text-secondary">Cédula de Identidad</label>
                            <input type="text" class="form-control shadow-sm border-2" id="editar_cedula_solicitante" name="cedula_solicitante">
                        </div>
                        <div class="col-md-9">
                            <label for="editar_nombre_solicitante" class="form-label fw-semibold small text-secondary">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm border-2" id="editar_nombre_solicitante" name="nombre_solicitante">
                        </div>
                        <div class="col-md-6">
                            <label for="editar_telefono1" class="form-label fw-semibold small text-secondary">Teléfono de Contacto 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm border-2" id="editar_telefono1" name="telefono1">
                        </div>
                        <div class="col-md-6">
                            <label for="editar_telefono2" class="form-label fw-semibold small text-secondary">Teléfono de Contacto 2</label>
                            <input type="text" class="form-control shadow-sm border-2" id="editar_telefono2" name="telefono2">
                        </div>
                    </div>

                    <!-- 3. BLOQUE: GEOLOCALIZACIÓN -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-geo-alt-fill me-2"></i>2. Ubicación de la Incidencia
                        </h6>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="editar_municipio_id" class="form-label fw-semibold small text-secondary">Municipio <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="editar_municipio_id" name="municipio_id">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($municipios as $municipio): ?>
                                    <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_parroquia_id" class="form-label fw-semibold small text-secondary">Parroquia <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="editar_parroquia_id" name="parroquia_id">
                                <option value="">-- Seleccionar Parroquia --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="editar_direccion_exacta" class="form-label fw-semibold small text-secondary">Referencia o Dirección Exacta <span class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm border-2" id="editar_direccion_exacta" name="direccion_exacta" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- 4. BLOQUE: DETALLES DE LA EMERGENCIA -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>3. Descripción de la Emergencia
                        </h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="editar_tipo_emergencia_id" class="form-label fw-semibold small text-secondary">Tipo de Incidente <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="editar_tipo_emergencia_id" name="tipo_emergencia_id">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($tiposEmergencia as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label for="editar_caso_id" class="form-label fw-semibold small text-secondary">Caso Específico <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="editar_caso_id" name="caso_id">
                                <option value="">-- Seleccionar Caso --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="editar_descripcion_caso" class="form-label fw-semibold small text-secondary">Resumen Técnico de la Situación <span class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm border-2" id="editar_descripcion_caso" name="descripcion_caso" rows="3"></textarea>
                        </div>
                    </div>

                </form>
            </div>

            <!-- 5. BLOQUE: ACCIONES DE CONTROL -->
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-ven-primary px-4 shadow-sm" id="btnGuardarEdicion">
                    <i class="bi bi-save-fill me-2"></i>Aplicar Cambios
                </button>
            </div>

        </div>
    </div>
</div>

