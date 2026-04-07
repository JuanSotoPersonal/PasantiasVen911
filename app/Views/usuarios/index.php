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
                  <div class="card-header">
                    <h3 class="card-title mb-0">
                      <i class="bi bi-people-fill me-2"></i>Listado de Usuarios
                    </h3>
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
                  <label for="crear-cedula" class="form-label fw-semibold">Cédula</label>
                  <input type="text" class="form-control" id="crear-cedula" name="cedula" placeholder="Ej: V-12345678" />
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
                  <label for="editar-cedula" class="form-label fw-semibold">Cédula</label>
                  <input type="text" class="form-control" id="editar-cedula" name="cedula" />
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
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector('.sidebar-wrapper');
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true },
          });
        }
      });
    </script>
    <!--fin::Configuración de OverlayScrollbars-->

    <!--inicio::Módulo Usuarios JS-->
    <script>
    $(function () {

      // ========================
      // 1. DATATABLE
      // ========================
      const tabla = $('#tablaUsuarios').DataTable({
        ajax: {
          url: 'index.php?url=usuario/getData',
          dataSrc: 'data',
          error: function () {
            Swal.fire('Error', 'No se pudieron cargar los datos de usuarios.', 'error');
          },
        },
        columns: [
          { data: null, width: '50px', orderable: false, searchable: false, render: (d, type, row, meta) => meta.row + 1 },
          { data: 'nombre_completo' },
          { data: 'usuario' },
          {
            data: 'cedula',
            render: (d) => d ? d : '<span class="text-muted fst-italic small">Sin cédula</span>',
          },
          { data: 'nombre_rol' },
          {
            data: 'codigo_operador',
            render: (d) => d ? `<code>${d}</code>` : '<span class="text-muted fst-italic small">—</span>',
          },
          {
            data: 'estado',
            render: (d, type, row) => {
              const isActivo = d === 'activo';
              const badgeClass = isActivo ? 'badge-activo' : 'badge-inactivo';
              const icon = isActivo ? 'bi-toggle-on' : 'bi-toggle-off';
              if (row.rol_id === 1) {
                return `
                <h3>
                  <span class="badge badge-estado ${badgeClass}">
                    <i class="bi bi-shield-lock-fill me-1"></i>Activo
                  </span>
                </h3>`;
            }else{
              return `
                <button
                  type="button"
                  class="btn-toggle-estado"
                  data-id="${row.id}"
                  data-estado="${d}"
                  title="Clic para cambiar estado"
                >
                  <span class="badge badge-estado ${badgeClass}">
                    <i class="bi ${icon} me-1"></i>${isActivo ? 'Activo' : 'Inactivo'}
                  </span>
                </button>`;
            }}
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: (d, type, row) => {
              if (row.rol_id === 1) {
                return `
                  <span class="btn-ven-edit btn-accion me-1 d-inline-flex align-items-center justify-content-center" 
                        style="cursor: help; opacity: 0.9;" title="Administrador Protegido">
                    <i class="bi bi-shield-lock-fill"></i>
                  </span>
                  <button
                    type="button"
                    class="btn btn-ven-password btn-accion btn-password"
                    data-id="${row.id}"
                    data-nombre="${row.nombre_completo}"
                    title="Cambiar contraseña"
                  >
                    <i class="bi bi-key-fill"></i>
                  </button>
                `;
              }
              return `
                <button
                  type="button"
                  class="btn btn-ven-edit btn-accion btn-editar me-1"
                  data-id="${row.id}"
                  data-nombre="${row.nombre_completo}"
                  data-cedula="${row.cedula || ''}"
                  data-usuario="${row.usuario}"
                  data-rol="${row.rol_id}"
                  data-id-rol="${row.rol_id}"
                  data-codigo="${row.codigo_operador || ''}"
                  title="Editar usuario"
                >
                  <i class="bi bi-pencil-fill"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-ven-password btn-accion btn-password"
                  data-id="${row.id}"
                  data-nombre="${row.nombre_completo}"
                  title="Cambiar contraseña"
                >
                  <i class="bi bi-key-fill"></i>
                </button>
              `;
            },
          },
        ],
        language: {
          url: '', // sin CDN — usamos traducción inline
          decimal:        ',',
          emptyTable:     'No hay usuarios registrados.',
          info:           'Mostrando _START_ a _END_ de _TOTAL_ usuarios',
          infoEmpty:      'Sin registros disponibles',
          infoFiltered:   '(filtrado de _MAX_ registros totales)',
          lengthMenu:     'Mostrar _MENU_ registros',
          loadingRecords: 'Cargando...',
          processing:     'Procesando...',
          search:         'Buscar:',
          zeroRecords:    'No se encontraron coincidencias.',
          paginate: {
            first:    '«',
            last:     '»',
            next:     '›',
            previous: '‹',
          },
        },
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 10,
      });

      // ========================
      // 2. HELPERS
      // ========================
      function recargarTabla() { tabla.ajax.reload(null, false); }

      function bloquearBtn($btn, texto = 'Procesando...') {
        $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span>${texto}`);
      }
      function desbloquearBtn($btn, html) { $btn.prop('disabled', false).html(html); }

      // Toggle eye icon en campos de contraseña
      $(document).on('click', '.btn-eye', function () {
        const targetId = $(this).data('target');
        const $input   = $('#' + targetId);
        const $icon    = $(this).find('i');
        if ($input.attr('type') === 'password') {
          $input.attr('type', 'text');
          $icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
          $input.attr('type', 'password');
          $icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
      });

      // ========================
      // 3. CREAR USUARIO
      // ========================
      $('#formCrearUsuario').on('submit', function (e) {
        e.preventDefault();
        const $btn   = $('#btn-guardar-crear');
        const $form  = $(this);
        const originalHtml = $btn.html();

        bloquearBtn($btn, 'Guardando...');

        $.ajax({
          url:    'index.php?url=usuario/store',
          method: 'POST',
          data:   $form.serialize(),
          dataType: 'json',
        })
        .done(function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: '¡Hecho!', text: res.message, timer: 2000, showConfirmButton: false });
            $('#modalCrearUsuario').modal('hide');
            $form[0].reset();
            recargarTabla();
          } else {
            Swal.fire({ icon: 'warning', title: 'Atención', text: res.message });
          }
        })
        .fail(function () {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor.' });
        })
        .always(function () {
          desbloquearBtn($btn, originalHtml);
        });
      });

      // Limpiar form al cerrar modal
      $('#modalCrearUsuario').on('hidden.bs.modal', function () {
        $('#formCrearUsuario')[0].reset();
      });

      // ========================
      // 4. EDITAR USUARIO
      // ========================
      $(document).on('click', '.btn-editar', function () {
        const $btn = $(this);
        $('#editar-id').val($btn.data('id'));
        $('#editar-nombre').val($btn.data('nombre'));
        $('#editar-cedula').val($btn.data('cedula'));
        $('#editar-usuario').val($btn.data('usuario'));
        $('#editar-rol').val($btn.data('rol'));
        $('#editar-codigo').val($btn.data('codigo'));
        $('#modalEditarUsuario').modal('show');
      });

      $('#formEditarUsuario').on('submit', function (e) {
        e.preventDefault();
        const $btn  = $('#btn-guardar-editar');
        const $form = $(this);
        const originalHtml = $btn.html();

        bloquearBtn($btn, 'Guardando...');

        $.ajax({
          url:    'index.php?url=usuario/update',
          method: 'POST',
          data:   $form.serialize(),
          dataType: 'json',
        })
        .done(function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: '¡Actualizado!', text: res.message, timer: 2000, showConfirmButton: false });
            $('#modalEditarUsuario').modal('hide');
            recargarTabla();
          } else {
            Swal.fire({ icon: 'warning', title: 'Atención', text: res.message });
          }
        })
        .fail(function () {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor.' });
        })
        .always(function () {
          desbloquearBtn($btn, originalHtml);
        });
      });

      // ========================
      // 5. CAMBIAR CONTRASEÑA
      // ========================
      $(document).on('click', '.btn-password', function () {
        const $btn = $(this);
        $('#pwd-id').val($btn.data('id'));
        $('#pwd-nombre-usuario').text($btn.data('nombre'));
        $('#pwd-nueva').val('').attr('type', 'password');
        $('#pwd-confirmar').val('').attr('type', 'password');
        $('#formCambiarPassword').find('.btn-eye i').removeClass('bi-eye-slash').addClass('bi-eye');
        $('#modalCambiarPassword').modal('show');
      });

      $('#formCambiarPassword').on('submit', function (e) {
        e.preventDefault();
        const $btn  = $(this).find('[type="submit"]');
        const $form = $(this);
        const originalHtml = $btn.html();

        bloquearBtn($btn, 'Actualizando...');

        $.ajax({
          url:    'index.php?url=usuario/updatePassword',
          method: 'POST',
          data:   $form.serialize(),
          dataType: 'json',
        })
        .done(function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: '¡Listo!', text: res.message, timer: 2000, showConfirmButton: false });
            $('#modalCambiarPassword').modal('hide');
            $form[0].reset();
          } else {
            Swal.fire({ icon: 'warning', title: 'Atención', text: res.message });
          }
        })
        .fail(function () {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor.' });
        })
        .always(function () {
          desbloquearBtn($btn, originalHtml);
        });
      });

      // ========================
      // 6. TOGGLE ESTADO
      // ========================
      $(document).on('click', '.btn-toggle-estado', function () {
        const id      = $(this).data('id');
        const estado  = $(this).data('estado');
        const accion  = estado === 'activo' ? 'desactivar' : 'activar';

        Swal.fire({
          title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} usuario?`,
          text:  `El usuario pasará a estado ${accion === 'activar' ? 'activo' : 'inactivo'}.`,
          icon:  'question',
          showCancelButton:  true,
          confirmButtonText: `Sí, ${accion}`,
          cancelButtonText:  'Cancelar',
          confirmButtonColor: '#16a34a',
          cancelButtonColor: '#f0fdf4',
        }).then(function (result) {
          if (!result.isConfirmed) return;

          $.ajax({
            url:    'index.php?url=usuario/toggleEstado',
            method: 'POST',
            data:   { id: id },
            dataType: 'json',
          })
          .done(function (res) {
            if (res.success) {
              Swal.fire({ icon: 'success', title: '¡Estado cambiado!', text: res.message, timer: 1800, showConfirmButton: false });
              recargarTabla();
            } else {
              Swal.fire({ icon: 'warning', title: 'Atención', text: res.message });
            }
          })
          .fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor.' });
          });
        });
      });

    }); // fin $(function)
    </script>
    <!--fin::Módulo Usuarios JS-->
    <!--fin::Scripts-->
  </body>
  <!--fin::Cuerpo-->
</html>
