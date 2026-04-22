    <!-- ============================================================
         MODAL: Crear Usuario
         ============================================================ -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header modal-header-ven">
            <h5 class="modal-title" id="modalCrearUsuarioLabel">
              <i class="bi bi-person-plus-fill me-2"></i>Agregar Nuevo Usuario
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formCrearUsuario" novalidate>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="crear-nombre" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="crear-nombre" name="nombre_completo" placeholder="Ej: Juan Pérez García" />
                  <div class="form-text mt-1">Nombre y apellido legal del usuario.</div>
                </div>
                <div class="col-md-6">
                  <label for="crear-cedula" class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">V-</span>
                    <input type="text" class="form-control" id="crear-cedula" name="cedula" placeholder="Ej: 12345678" />
                  </div>
                  <div class="form-text">Solo números (entre 6 y 8).</div>
                </div>
                <div class="col-md-6">
                  <label for="crear-usuario" class="form-label fw-semibold">Usuario (login) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="crear-usuario" name="usuario" placeholder="Mín. 7 caracteres, sin espacios" />
                  <div class="form-text mt-1">Nombre de usuario único para acceder al sistema.</div>
                </div>

                <div class="col-md-6">
                  <label for="crear-rol" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                  <select class="form-select" id="crear-rol" name="rol_id">
                    <option value="">-- Seleccionar rol --</option>
                    <?php foreach ($roles as $rol): ?>
                      <?php if ($rol['id'] == 1) continue; ?>
                      <option value="<?= htmlspecialchars((string)$rol['id']) ?>">
                        <?= htmlspecialchars($rol['nombre']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text mt-1">Define los permisos de acceso al sistema.</div>
                </div>
                <!-- Sección de Seguridad Oculta (SuperAdmin Único vía Setup) -->
                <div id="seccion-seguridad-crear" style="display: none;"></div>
                <div class="col-md-6">
                  <label for="crear-password" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                  <div class="password-wrapper">
                    <input type="password" class="form-control pe-5" id="crear-password" name="password" placeholder="Mín. 6 caracteres" />
                    <button type="button" class="btn-eye" data-target="crear-password" title="Ver contraseña">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                  <div class="form-text mt-1">Mínimo 6 caracteres (alfanumérico).</div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ven-cancel" data-bs-dismiss="modal">
                <i class="bi bi-x-lg me-1"></i>Cancelar
              </button>
              <button type="submit" class="btn btn-ven-primary" id="btn-guardar-crear">
                <i class="bi bi-check-lg me-1"></i>Guardar Usuario
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
