<!-- MODAL EDITAR FICHA -->
<div class="modal fade" id="modalEditarFicha" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header modal-header-ven">
        <h5 class="modal-title text-white">
          <i class="bi bi-pencil-square me-2"></i>Editar Ficha <span id="editarFichaIdLabel">#</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditarFicha" novalidate>
          <input type="hidden" name="ficha_id" id="editar_ficha_id">

          <h6 class="fw-bold text-ven-green border-bottom pb-2 mb-3">
            <i class="bi bi-person-fill me-2"></i>Datos del Solicitante
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar_cedula_solicitante" class="form-label">Cédula</label>
              <input type="text" class="form-control" id="editar_cedula_solicitante" name="cedula_solicitante" maxlength="12">
            </div>
            <div class="col-md-8">
              <label for="editar_nombre_solicitante" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editar_nombre_solicitante" name="nombre_solicitante">
            </div>
            <div class="col-md-6">
              <label for="editar_telefono1" class="form-label">Teléfono Principal <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editar_telefono1" name="telefono1">
            </div>
            <div class="col-md-6">
              <label for="editar_telefono2" class="form-label">Teléfono Secundario</label>
              <input type="text" class="form-control" id="editar_telefono2" name="telefono2">
            </div>
          </div>

          <h6 class="fw-bold text-ven-green border-bottom pb-2 mb-3">
            <i class="bi bi-geo-alt-fill me-2"></i>Ubicación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="editar_municipio_id" class="form-label">Municipio <span class="text-danger">*</span></label>
              <select class="form-select" id="editar_municipio_id" name="municipio_id">
                <option value="">-- Seleccione --</option>
                <?php foreach ($municipios as $municipio): ?>
                  <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editar_parroquia_id" class="form-label">Parroquia <span class="text-danger">*</span></label>
              <select class="form-select" id="editar_parroquia_id" name="parroquia_id">
                <option value="">-- Seleccione municipio --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="editar_direccion_exacta" class="form-label">Dirección Exacta <span class="text-danger">*</span></label>
              <textarea class="form-control" id="editar_direccion_exacta" name="direccion_exacta" rows="2"></textarea>
            </div>
          </div>

          <h6 class="fw-bold text-ven-green border-bottom pb-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Emergencia
          </h6>
          <div class="row g-3">
            <div class="col-md-5">
              <label for="editar_tipo_emergencia_id" class="form-label">Tipo <span class="text-danger">*</span></label>
              <select class="form-select" id="editar_tipo_emergencia_id" name="tipo_emergencia_id">
                <option value="">-- Seleccione --</option>
                <?php foreach ($tiposEmergencia as $tipo): ?>
                  <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-7">
              <label for="editar_caso_id" class="form-label">Caso <span class="text-danger">*</span></label>
              <select class="form-select" id="editar_caso_id" name="caso_id">
                <option value="">-- Seleccione tipo --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="editar_descripcion_caso" class="form-label">Descripción <span class="text-danger">*</span></label>
              <textarea class="form-control" id="editar_descripcion_caso" name="descripcion_caso" rows="3"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-ven-primary" id="btnGuardarEdicion">
          <i class="bi bi-save-fill me-1"></i>Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</div>
