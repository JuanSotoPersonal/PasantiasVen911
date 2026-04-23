<?php
/**
 * _modal_crear.php - Formulario de Captura de Emergencias
 * 
 * Interfaz modal para la creación de nuevas fichas de despacho.
 * Organizado en 3 bloques lógicos para optimizar la velocidad de carga de datos.
 */
?>

<div class="modal fade" id="modalCrearFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <!-- 1. BLOQUE: CABECERA INSTITUCIONAL -->
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white">
                    <i class="bi bi-file-earmark-plus-fill me-2"></i>Apertura de Ficha de Emergencia Ven911
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formCrearFicha" novalidate>

                    <!-- 2. BLOQUE: IDENTIFICACIÓN DEL SOLICITANTE -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-person-badge-fill me-2"></i>1. Identificación del Solicitante
                        </h6>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="crear_cedula_solicitante" class="form-label fw-semibold small text-secondary">Cédula de Identidad</label>
                            <input type="text" class="form-control shadow-sm border-2" id="crear_cedula_solicitante" name="cedula_solicitante" placeholder="V-00000000">
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Opcional. Formato numérico sin puntos.</div>
                        </div>
                        <div class="col-md-9">
                            <label for="crear_nombre_solicitante" class="form-label fw-semibold small text-secondary">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm border-2" id="crear_nombre_solicitante" name="nombre_solicitante" placeholder="Apellidos y nombres del reportante">
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Mínimo 3 caracteres. Identificación clara del reportante.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="crear_telefono1" class="form-label fw-semibold small text-secondary">Teléfono de Contacto 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm border-2" id="crear_telefono1" name="telefono1" placeholder="Ej: 0412-0000000">
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Obligatorio. 11 dígitos numéricos.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="crear_telefono2" class="form-label fw-semibold small text-secondary">Teléfono de Contacto 2 (Opcional)</label>
                            <input type="text" class="form-control shadow-sm border-2" id="crear_telefono2" name="telefono2" placeholder="Alternativo">
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Opcional. Teléfono secundario para contacto.</div>
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
                            <label for="crear_municipio_id" class="form-label fw-semibold small text-secondary">Municipio <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="crear_municipio_id" name="municipio_id">
                                <option value="">-- Seleccionar Municipio --</option>
                                <?php foreach ($municipios as $municipio): ?>
                                    <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Jurisdicción donde ocurre el evento.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="crear_parroquia_id" class="form-label fw-semibold small text-secondary">Parroquia <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="crear_parroquia_id" name="parroquia_id" disabled>
                                <option value="">-- Seleccionar Parroquia --</option>
                            </select>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Habilitado al seleccionar un municipio.</div>
                        </div>
                        <div class="col-12">
                            <label for="crear_direccion_exacta" class="form-label fw-semibold small text-secondary">Referencia o Dirección Exacta <span class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm border-2" id="crear_direccion_exacta" name="direccion_exacta" rows="2" placeholder="Sector, calle, casa, puntos de referencia..."></textarea>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Indique puntos clave para la llegada de las unidades.</div>
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
                            <label for="crear_tipo_emergencia_id" class="form-label fw-semibold small text-secondary">Tipo de Incidente <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="crear_tipo_emergencia_id" name="tipo_emergencia_id">
                                <option value="">-- Seleccionar Tipo --</option>
                                <?php foreach ($tiposEmergencia as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Categoría macro del incidente.</div>
                        </div>
                        <div class="col-md-7">
                            <label for="crear_caso_id" class="form-label fw-semibold small text-secondary">Caso Específico <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm border-2" id="crear_caso_id" name="caso_id" disabled>
                                <option value="">-- Seleccionar Caso --</option>
                            </select>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Clasificación detallada para despacho.</div>
                        </div>
                        <div class="col-12">
                            <label for="crear_descripcion_caso" class="form-label fw-semibold small text-secondary">Resumen Técnico de la Situación <span class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm border-2" id="crear_descripcion_caso" name="descripcion_caso" rows="3" placeholder="Detalle la emergencia según el reporte telefónico..."></textarea>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Relato cronológico y estado actual de la situación.</div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- 5. BLOQUE: ACCIONES DE CONTROL -->
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">Descartar</button>
                <button type="button" class="btn btn-ven-primary px-4 shadow-sm" id="btnGuardarFicha">
                    <i class="bi bi-save-fill me-2"></i>Consolidar Ficha
                </button>
            </div>

        </div>
    </div>
</div>

