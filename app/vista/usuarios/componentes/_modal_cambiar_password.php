    <!-- ============================================================
         MODAL: Cambiar Contraseña
         ============================================================ -->
    <div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header modal-header-ven">
            <h5 class="modal-title" id="modalPasswordLabel">
              <i class="bi bi-shield-lock-fill me-2"></i>Cambiar Contraseña
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formCambiarPassword" novalidate>
            <input type="hidden" id="pwd-id" name="id" />
            <div class="modal-body">
              <p class="mb-3 text-muted small">
                Estableciendo nueva contraseña para: <strong id="pwd-nombre-usuario"></strong>
              </p>
              <div class="mb-3">
                <label for="pwd-nueva" class="form-label fw-semibold">Nueva Contraseña <span class="text-danger">*</span></label>
                <div class="password-wrapper">
                  <input type="password" class="form-control pe-5" id="pwd-nueva" name="password" placeholder="Mín. 6 caracteres" />
                  <button type="button" class="btn-eye" data-target="pwd-nueva" title="Ver contraseña">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="form-text mt-1">La nueva contraseña debe ser segura.</div>
              </div>
              <div class="mb-3">
                <label for="pwd-confirmar" class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                <div class="password-wrapper">
                  <input type="password" class="form-control pe-5" id="pwd-confirmar" name="password_confirm" placeholder="Repite la contraseña" />
                  <button type="button" class="btn-eye" data-target="pwd-confirmar" title="Ver contraseña">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="form-text mt-1">Repita la nueva contraseña exactamente.</div>
              </div>
              <!-- Sección de Validación de Seguridad (Autocargado vía JS si es SuperAdmin) -->
              <div id="seccion-validacion-seguridad" class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded" style="display: none;">
                 <h6 class="text-dark fw-bold mb-2 small"><i class="bi bi-shield-fill-exclamation me-2"></i>Verificación de Identidad</h6>
                 <div class="mb-2">
                    <label id="label-pregunta-1" class="form-label small fw-bold mb-1"></label>
                    <input type="text" class="form-control form-control-sm" id="ans-1" name="ans_1" placeholder="Respuesta 1">
                 </div>
                 <div class="mb-0">
                    <label id="label-pregunta-2" class="form-label small fw-bold mb-1"></label>
                    <input type="text" class="form-control form-control-sm" id="ans-2" name="ans_2" placeholder="Respuesta 2">
                 </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">
                <i class="bi bi-x-lg me-1"></i>Cancelar
              </button>
              <button type="submit" class="btn btn-ven-password">
                <i class="bi bi-shield-check me-1"></i>Actualizar Contraseña
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
