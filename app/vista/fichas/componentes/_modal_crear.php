<!-- MODAL CREAR FICHA -->
<div class="modal fade" id="modalCrearFicha" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header modal-header-ven">
        <h5 class="modal-title text-white">
          <i class="bi bi-file-earmark-plus-fill me-2"></i>Nueva Ficha de Emergencia
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCrearFicha" novalidate>

          <!-- SECCIÓN: DATOS DEL SOLICITANTE -->
          <h6 class="fw-bold text-ven-green border-bottom pb-2 mb-3">
            <i class="bi bi-person-fill me-2"></i>Datos del Solicitante
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="crear_cedula_solicitante" class="form-label">Cédula <small class="text-muted">(opcional)</small></label>
              <input type="text" class="form-control" id="crear_cedula_solicitante" name="cedula_solicitante"
                     placeholder="Ej: 12345678" maxlength="12">
            </div>
            <div class="col-md-8">
              <label for="crear_nombre_solicitante" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="crear_nombre_solicitante" name="nombre_solicitante"
                     placeholder="Nombre y apellido del solicitante">
            </div>
            <div class="col-md-6">
              <label for="crear_telefono1" class="form-label">Teléfono Principal <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="crear_telefono1" name="telefono1"
                     placeholder="0412-1234567">
            </div>
            <div class="col-md-6">
              <label for="crear_telefono2" class="form-label">Teléfono Secundario</label>
              <input type="text" class="form-control" id="crear_telefono2" name="telefono2"
                     placeholder="Opcional">
            </div>
          </div>

          <!-- SECCIÓN: UBICACIÓN -->
          <h6 class="fw-bold text-ven-green border-bottom pb-2 mb-3">
            <i class="bi bi-geo-alt-fill me-2"></i>Ubicación de la Emergencia
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="crear_municipio_id" class="form-label">Municipio <span class="text-danger">*</span></label>
              <select class="form-select" id="crear_municipio_id" name="municipio_id">
                <option value="">-- Seleccione municipio --</option>
                <?php foreach ($municipios as $municipio): ?>
                  <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nombre_municipio']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="crear_parroquia_id" class="form-label">Parroquia <span class="text-danger">*</span></label>
              <select class="form-select" id="crear_parroquia_id" name="parroquia_id" disabled>
                <option value="">-- Primero seleccione municipio --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="crear_direccion_exacta" class="form-label">Dirección Exacta <span class="text-danger">*</span></label>
              <textarea class="form-control" id="crear_direccion_exacta" name="direccion_exacta"
                        rows="2" placeholder="Calle, avenida, sector, referencia..."></textarea>
            </div>
          </div>

          <!-- SECCIÓN: EMERGENCIA -->
          <h6 class="fw-bold text-ven-green border-bottom pb-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Tipo de Emergencia
          </h6>
          <div class="row g-3">
            <div class="col-md-5">
              <label for="crear_tipo_emergencia_id" class="form-label">Tipo de Emergencia <span class="text-danger">*</span></label>
              <select class="form-select" id="crear_tipo_emergencia_id" name="tipo_emergencia_id">
                <option value="">-- Seleccione tipo --</option>
                <?php foreach ($tiposEmergencia as $tipo): ?>
                  <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-7">
              <label for="crear_caso_id" class="form-label">Caso <span class="text-danger">*</span></label>
              <select class="form-select" id="crear_caso_id" name="caso_id" disabled>
                <option value="">-- Primero seleccione tipo --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="crear_descripcion_caso" class="form-label">Descripción Adicional <span class="text-danger">*</span></label>
              <textarea class="form-control" id="crear_descripcion_caso" name="descripcion_caso"
                        rows="3" placeholder="Describa la situación con el mayor detalle posible..."></textarea>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-ven-primary" id="btnGuardarFicha">
          <i class="bi bi-save-fill me-1"></i>Registrar Ficha
        </button>
      </div>
    </div>
  </div>
</div>
