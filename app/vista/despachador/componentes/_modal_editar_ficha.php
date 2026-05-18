<?php
/**
 * _modal_editar_ficha.php — Componente: Edición Operacional de Ficha
 *
 * Formulario de edición rápida para despachadores.
 * Permite actualizar los campos operacionales clave:
 *   - Descripción del caso (actualizable a medida que llega más info)
 *   - Dirección exacta (puede corregirse en campo)
 *   - Teléfonos del solicitante (contacto de seguimiento)
 *
 * NO modifica: tipo de caso, parroquia/municipio ni creador original.
 * Accesible solo para Despachador y Administrador (tienePerm 'fichas' → 'editar').
 */
?>

<div class="modal fade" id="modalEditarFicha" tabindex="-1"
     aria-labelledby="modalEditarFichaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <!-- Encabezado institucional -->
            <div class="modal-header modal-header-ven py-3">
                <h5 class="modal-title text-white" id="modalEditarFichaLabel">
                    <i class="bi bi-pencil-square me-2"></i>Editar Ficha
                    <span id="editarFichaIdLabel" class="ms-1 opacity-75"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Formulario de edición operacional -->
            <div class="modal-body p-4">
                <form id="formEditarFicha" novalidate>
                    <input type="hidden" id="editar_ficha_id" name="ficha_id">

                    <!-- Sección 1: Datos de contacto del solicitante -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-telephone-fill me-2"></i>1. Contacto del Solicitante
                        </h6>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="editar_telefono1" class="form-label fw-semibold small text-secondary">
                                Teléfono de Contacto 1 <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="editar_telefono1" name="telefono1"
                                   class="form-control shadow-sm border-2" placeholder="Ej: 0412-0000000">
                            <div class="form-text" style="font-size:0.75rem;">11 dígitos numéricos. Obligatorio.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="editar_telefono2" class="form-label fw-semibold small text-secondary">
                                Teléfono de Contacto 2 <span class="text-muted">(Opcional)</span>
                            </label>
                            <input type="text" id="editar_telefono2" name="telefono2"
                                   class="form-control shadow-sm border-2" placeholder="Alternativo">
                        </div>
                    </div>

                    <!-- Sección 2: Ubicación Geográfica (Solo Despacho) -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-geo-alt-fill me-2"></i>2. Ubicación Geográfica
                        </h6>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <!-- Parroquia Id Oculto para Cascada -->
                        <input type="hidden" id="editar_despacho_parroquia_id" name="parroquia_id">
                        
                        <div class="col-md-6">
                            <label for="editar_despacho_comuna_id" class="form-label fw-semibold small text-secondary">Comuna (Opcional)</label>
                            <select class="form-select shadow-sm border-2" id="editar_despacho_comuna_id" name="comuna_id" disabled>
                                <option value="">-- Seleccionar Comuna --</option>
                            </select>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Para refinar la ubicación.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_despacho_sector_id" class="form-label fw-semibold small text-secondary">Sector (Opcional)</label>
                            <select class="form-select shadow-sm border-2" id="editar_despacho_sector_id" name="sector_id" disabled>
                                <option value="">-- Seleccionar Sector --</option>
                            </select>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Habilitado al seleccionar comuna.</div>
                        </div>
                    </div>

                    <!-- Sección 3: Datos operacionales de la emergencia -->
                    <div class="section-title-container mb-4">
                        <h6 class="fw-bold text-success text-uppercase small border-bottom pb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>3. Datos de la Emergencia
                        </h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="editar_direccion_exacta" class="form-label fw-semibold small text-secondary">
                                Dirección / Referencia Exacta <span class="text-danger">*</span>
                            </label>
                            <textarea id="editar_direccion_exacta" name="direccion_exacta"
                                      class="form-control shadow-sm border-2" rows="2"
                                      placeholder="Sector, calle, casa, puntos de referencia..."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="editar_descripcion_caso" class="form-label fw-semibold small text-secondary">
                                Descripción / Novedades del Caso <span class="text-danger">*</span>
                            </label>
                            <textarea id="editar_descripcion_caso" name="descripcion_caso"
                                      class="form-control shadow-sm border-2" rows="4"
                                      placeholder="Actualice el relato del caso con las novedades recibidas..."></textarea>
                            <div class="form-text" style="font-size:0.75rem;">Puede ampliar la información conforme avance la emergencia.</div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Pie del modal -->
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-ven-cancel px-4" data-bs-dismiss="modal">
                    Descartar
                </button>
                <button type="button" id="btnGuardarEdicionFicha" class="btn btn-ven-primary px-4 shadow-sm">
                    <i class="bi bi-save-fill me-2"></i>Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>
