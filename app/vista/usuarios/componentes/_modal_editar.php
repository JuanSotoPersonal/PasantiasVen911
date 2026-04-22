    <!-- ============================================================
         MODAL: Editar Usuario
         ============================================================ -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header modal-header-ven">
            <h5 class="modal-title" id="modalEditarUsuarioLabel">
              <i class="bi bi-pencil-square me-2"></i>Editar Usuario
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formEditarUsuario" novalidate>
            <input type="hidden" id="editar-id" name="id" />
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="editar-nombre" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editar-nombre" name="nombre_completo" />
                  <div class="form-text mt-1">Actualice el nombre si es necesario.</div>
                </div>
                <div class="col-md-6">
                  <label for="editar-cedula" class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">V-</span>
                    <input type="text" class="form-control" id="editar-cedula" name="cedula" />
                  </div>
                  <div class="form-text mt-1">Modificar solo si hubo un error en el registro inicial.</div>
                </div>
                <div class="col-md-6">
                  <label for="editar-usuario" class="form-label fw-semibold">Usuario (login) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editar-usuario" name="usuario" />
                  <div class="form-text mt-1">Nombre de usuario (login).</div>
                </div>

                <div class="col-md-6">
                  <label for="editar-rol" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                  <select class="form-select" id="editar-rol" name="rol_id">
                    <?php foreach ($roles as $rol): ?>
                      <?php if ($rol['id'] == 1) continue; ?>
                      <option value="<?= htmlspecialchars((string)$rol['id']) ?>">
                        <?= htmlspecialchars($rol['nombre']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text mt-1">Seleccione el rol que define los permisos del usuario.</div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">
                <i class="bi bi-x-lg me-1"></i>Cancelar
              </button>
              <button type="submit" class="btn btn-ven-edit" id="btn-guardar-editar">
                <i class="bi bi-save me-1"></i>Guardar Cambios
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
