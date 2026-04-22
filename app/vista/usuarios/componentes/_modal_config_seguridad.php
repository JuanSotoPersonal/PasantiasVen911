    <!-- ============================================================
         MODAL: Actualizar Preguntas de Seguridad (SuperAdmin)
         ============================================================ -->
    <div class="modal fade" id="modalConfigSeguridad" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header modal-header-ven">
            <h5 class="modal-title">
              <i class="bi bi-shield-lock-fill me-2"></i>Configurar Preguntas de Seguridad
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formConfigSeguridad" novalidate>
            <input type="hidden" id="seg-id" name="id" />
            <div class="modal-body">
              <div class="alert alert-info py-2 small">
                <i class="bi bi-info-circle me-1"></i> Requiere el <strong>Código de Fábrica</strong> para autorizar el cambio.
              </div>
              
              <div class="mb-3">
                <label for="seg-factory-code" class="form-label fw-bold">Código de Fábrica</label>
                <input type="text" class="form-control border-danger border-opacity-50" id="seg-factory-code" name="factory_code" placeholder="XXXX-XXXX-XXXX">
                <div class="form-text mt-1">Código de 12 dígitos para autorizar el cambio de preguntas.</div>
              </div>

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label small fw-bold">Nueva Pregunta 1</label>
                  <select class="form-select form-select-sm" name="pregunta_1">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($preguntas as $p): ?>
                      <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pregunta']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text mt-1">Seleccione una pregunta válida.</div>
                </div>
                <div class="col-12">
                  <input type="text" class="form-control form-control-sm" name="respuesta_1" placeholder="Nueva respuesta 1">
                  <div class="form-text mt-1">Respuesta a la primera pregunta de seguridad.</div>
                </div>
                <div class="col-12">
                  <label class="form-label small fw-bold">Nueva Pregunta 2</label>
                  <select class="form-select form-select-sm" name="pregunta_2">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($preguntas as $p): ?>
                      <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pregunta']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text mt-1">Esta pregunta será usada como respaldo secundario.</div>
                  </div>
                <div class="col-12">
                  <input type="text" class="form-control form-control-sm" name="respuesta_2" placeholder="Nueva respuesta 2">
                  <div class="form-text mt-1">Respuesta a la segunda pregunta de seguridad.</div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-ven-primary">
                <i class="bi bi-check-circle me-1"></i>Actualizar Seguridad
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
