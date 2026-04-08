<!doctype html>
<html lang="es">
  <!--inicio::Encabezado-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ven911 | Gestión de Usuarios</title>

    <!--inicio::Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#16a34a" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#064e3b" media="(prefers-color-scheme: dark)" />
    <meta name="title" content="Ven911 | Gestión de Usuarios" />
    <meta name="description" content="Módulo de gestión de usuarios del sistema Ven911." />
    <!--fin::Meta Tags-->

    <!--inicio::Estilos-->
    <link rel="preload" href="public/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="public/css/adminlte.css" />
    <link rel="stylesheet" href="public/css/home.css" />
    <link rel="stylesheet" href="public/css/usuarios.css" />
    <link rel="stylesheet" href="public/libs/source-sans-3/index.css" />
    <link rel="stylesheet" href="public/libs/overlayscrollbars/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="public/libs/bootstrap-icons/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="public/libs/sweetalert2/sweetalert2.min.css" />
    <!-- DataTables + Bootstrap 5 -->
    <link rel="stylesheet" href="public/libs/datatables/dataTables.bootstrap5.min.css" />
    <!--fin::Estilos-->
  </head>
  <!--fin::Encabezado-->

  <!--inicio::Cuerpo-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--inicio::Contenedor de la Aplicación-->
    <div class="app-wrapper">

      <?php require __DIR__ . '/../partials/navbar.php'; ?>
      <?php require __DIR__ . '/../partials/sidebar.php'; ?>

      <!--inicio::Principal de la Aplicación-->
      <main class="app-main">
        <!--inicio::Encabezado de Contenido-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0">Gestión de Usuarios</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="index.php?url=home">Inicio</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--fin::Encabezado de Contenido-->

        <!--inicio::Contenido de la Aplicación-->
        <div class="app-content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <!--inicio::Card Tabla de Usuarios-->
                <div class="card card-usuarios">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                      <i class="bi bi-people-fill me-2"></i>Listado de Usuarios
                    </h3>
                    <span class="badge bg-success fs-6 ms-2" id="badge-count-total">— usuarios</span>
                    <div class="card-tools ms-auto">
                      <button
                        type="button"
                        class="btn btn-ven-primary btn-sm"
                        id="btn-abrir-modal-crear"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCrearUsuario"
                      >
                        <i class="bi bi-person-plus-fill me-1"></i> Agregar Usuario
                      </button>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table
                        id="tablaUsuarios"
                        class="table table-bordered table-striped table-hover align-middle"
                        style="width:100%"
                      >
                        <thead class="table-dark">
                          <tr>
                            <th>#</th>
                            <th>Nombre Completo</th>
                            <th>Usuario (Cédula)</th>
                            <th>Cédula</th>
                            <th>Rol</th>
                            <th>Cód. Operador</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                          </tr>
                        </thead>
                        <tbody id="tbody-usuarios">
                          <!-- DataTables lo llena via AJAX -->
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <!--fin::Card Tabla de Usuarios-->

                <!--inicio::Tablas por Rol-->
                <?php
                    $iconosPorRol = [
                        'Administrador' => 'bi-shield-fill',
                        'Despachador'   => 'bi-broadcast',
                        'Operador'      => 'bi-headset',
                        'Jefe'          => 'bi-star-fill',
                    ];
                    foreach ($roles as $rol):
                        if ($rol['id'] == 1) continue;
                        $rolId     = (int)$rol['id'];
                        $rolNombre = htmlspecialchars($rol['nombre']);
                        $tablaId   = "tablaRol{$rolId}";
                        $icono     = $iconosPorRol[$rolNombre] ?? 'bi-person-badge';
                ?>
                <div class="col-12 mt-4">
                  <div class="card card-usuarios">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h3 class="card-title mb-0">
                        <i class="bi <?= $icono ?> me-2"></i><?= $rolNombre ?>s Registrados
                      </h3>
                      <span class="badge bg-success fs-6" id="badge-count-<?= $rolId ?>">— usuarios</span>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table
                          id="<?= $tablaId ?>"
                          class="table table-bordered table-striped table-hover align-middle"
                          style="width:100%"
                          data-rol-id="<?= $rolId ?>"
                        >
                          <thead class="table-dark">
                            <tr>
                              <th>#</th>
                              <th>Nombre Completo</th>
                              <th>Usuario (Cédula)</th>
                              <th>Cédula</th>
                              <th>Cód. Operador</th>
                              <th>Estado</th>
                              <th class="text-center">Acciones</th>
                            </tr>
                          </thead>
                          <tbody><!-- DataTables AJAX --></tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
                <!--fin::Tablas por Rol-->

              </div>
            </div>
          </div>
        </div>
        <!--fin::Contenido de la Aplicación-->
      </main>
      <!--fin::Principal de la Aplicación-->

      <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>
    <!--fin::Contenedor de la Aplicación-->

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
                  <input type="text" class="form-control" id="crear-nombre" name="nombre_completo" placeholder="Ej: Juan Pérez García" required />
                </div>
                <div class="col-md-6">
                  <label for="crear-cedula" class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">V-</span>
                    <input type="text" class="form-control" id="crear-cedula" name="cedula" placeholder="Ej: 12345678" minlength="6" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required />
                  </div>
                  <div class="form-text">Solo números (entre 6 y 8).</div>
                </div>
                <div class="col-md-6">
                  <label for="crear-usuario" class="form-label fw-semibold">Usuario (login) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="crear-usuario" name="usuario" placeholder="Mín. 7 caracteres, sin espacios" required />
                  <div class="form-text">Solo letras y números. Sin signos especiales.</div>
                </div>
                <div class="col-md-6">
                  <label for="crear-codigo" class="form-label fw-semibold">Código Operador</label>
                  <input type="text" class="form-control" id="crear-codigo" name="codigo_operador" placeholder="Ej: OP-001" />
                </div>
                <div class="col-md-6">
                  <label for="crear-rol" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                  <select class="form-select" id="crear-rol" name="rol_id" required>
                    <option value="">-- Seleccionar rol --</option>
                    <?php foreach ($roles as $rol): ?>
                      <?php if ($rol['id'] == 1) continue; ?>
                      <option value="<?= htmlspecialchars((string)$rol['id']) ?>">
                        <?= htmlspecialchars($rol['nombre']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="crear-password" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                  <div class="password-wrapper">
                    <input type="password" class="form-control pe-5" id="crear-password" name="password" placeholder="Mín. 6 caracteres" required />
                    <button type="button" class="btn-eye" data-target="crear-password" title="Ver contraseña">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
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
                  <input type="text" class="form-control" id="editar-nombre" name="nombre_completo" required />
                </div>
                <div class="col-md-6">
                  <label for="editar-cedula" class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">V-</span>
                    <input type="text" class="form-control" id="editar-cedula" name="cedula" minlength="6" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required />
                  </div>
                  <div class="form-text">Solo números (entre 6 y 8).</div>
                </div>
                <div class="col-md-6">
                  <label for="editar-usuario" class="form-label fw-semibold">Usuario (login) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editar-usuario" name="usuario" required />
                </div>
                <div class="col-md-6">
                  <label for="editar-codigo" class="form-label fw-semibold">Código Operador</label>
                  <input type="text" class="form-control" id="editar-codigo" name="codigo_operador" />
                </div>
                <div class="col-md-6">
                  <label for="editar-rol" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                  <select class="form-select" id="editar-rol" name="rol_id" required>
                    <?php foreach ($roles as $rol): ?>
                      <?php if ($rol['id'] == 1) continue; ?>
                      <option value="<?= htmlspecialchars((string)$rol['id']) ?>">
                        <?= htmlspecialchars($rol['nombre']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
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
                  <input type="password" class="form-control pe-5" id="pwd-nueva" name="password" placeholder="Mín. 6 caracteres" required />
                  <button type="button" class="btn-eye" data-target="pwd-nueva" title="Ver contraseña">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
              <div class="mb-3">
                <label for="pwd-confirmar" class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                <div class="password-wrapper">
                  <input type="password" class="form-control pe-5" id="pwd-confirmar" name="password_confirm" placeholder="Repite la contraseña" required />
                  <button type="button" class="btn-eye" data-target="pwd-confirmar" title="Ver contraseña">
                    <i class="bi bi-eye"></i>
                  </button>
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

    <!--inicio::Scripts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="public/libs/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="public/libs/popperjs/popper.min.js"></script>
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script src="public/libs/bootstrap/bootstrap.min.js"></script>
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="public/js/adminlte.js"></script>
    <!-- SweetAlert2 -->
    <script src="public/libs/sweetalert2/sweetalert2.min.js"></script>
    <!-- jQuery (requerido por DataTables) -->
    <script src="public/libs/datatables/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="public/libs/datatables/dataTables.min.js"></script>
    <script src="public/libs/datatables/dataTables.bootstrap5.min.js"></script>

    <!--inicio::Configuración de OverlayScrollbars-->
    <script src="public/js/usuarios/overlay.js"></script>
    <!--fin::Configuración de OverlayScrollbars-->

    <!--inicio::Módulo Usuarios JS-->
    <script src="public/js/usuarios/datatable.js"></script>
    <script src="public/js/usuarios/roles.js"></script>
    <!--fin::Módulo Usuarios JS-->
    <!--fin::Scripts-->

  </body>
  <!--fin::Cuerpo-->
</html>
