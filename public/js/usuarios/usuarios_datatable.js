// usuarios/datatable.js
// Módulo Usuarios: DataTable, CRUD (crear, editar, contraseña, toggle estado)

$(function () {

  // Configuración global de SweetAlert para evitar que modifique el padding y altura del body y achique la vista
  window.Swal = Swal.mixin({
    heightAuto: false,
    scrollbarPadding: false
  });

  // ========================
  // 1. Opciones comunes para DataTables
  // ========================
  const configuracionColumnas = [
    { data: null, width: '50px', orderable: false, searchable: false, render: (d, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
    { data: 'nombre_completo', render: (d) => escapeHTML(d) },
    { data: 'usuario', render: (d) => escapeHTML(d) },
    {
      data: 'cedula',
      render: (d) => d ? `V-${escapeHTML(d)}` : '<span class="text-muted fst-italic small">Sin cédula</span>',
    },
    { data: 'nombre_rol', render: (d) => escapeHTML(d) },
    {
      data: 'estado',
      render: (d, type, row) => {
        const isActivo = d === 'activo';
        const badgeClass = isActivo ? 'badge-activo' : 'badge-inactivo';
        const icon = isActivo ? 'bi-toggle-on' : 'bi-toggle-off';

        // Si es SuperAdmin (id 1), el estado es estático por seguridad
        if (row.rol_id == 1) {
          return `
          <h3>
            <span class="badge badge-estado ${badgeClass}">
              <i class="bi bi-shield-lock-fill me-1"></i>Activo
            </span>
          </h3>`;
        }

        // Si NO tiene permiso de edición, mostramos el badge estático (sin botón)
        if (!window.VEN911_PERM_EDITAR) {
          return `
          <h3>
            <span class="badge badge-estado ${badgeClass}">
              <i class="bi ${isActivo ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i>${isActivo ? 'Activo' : 'Inactivo'}
            </span>
          </h3>`;
        }

        // Si tiene permiso, renderizamos el botón interactivo
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
      },
    },
    {
      data: null,
      orderable: false,
      searchable: false,
      className: 'text-center',
      render: (d, type, row) => {
        const htmlActions = [];

        // CASO A: La fila es un Administrador (Rol 1)
        if (row.rol_id == 1) {
          // Icono estático de escudo
          htmlActions.push(`
            <span class="btn-ven-edit btn-accion me-1 d-inline-flex align-items-center justify-content-center"
                  style="cursor: help; opacity: 0.9;" title="Administrador Protegido">
              <i class="bi bi-shield-lock-fill"></i>
            </span>
          `);

          // Botón Password (Solo si el logueado es SuperAdmin O tiene permiso editar)
          // Nota: Solo el SuperAdmin puede cambiar password de otro SuperAdmin usualmente
          if (window.USER_ROL_ID === 1) {
            htmlActions.push(`
              <button type="button" class="btn btn-ven-password btn-accion btn-password"
                data-id="${row.id}" data-nombre="${escapeHTML(row.nombre_completo)}" title="Cambiar contraseña">
                <i class="bi bi-key-fill"></i>
              </button>
            `);
            htmlActions.push(`
              <button type="button" class="btn btn-ven-primary btn-accion btn-config-seguridad"
                data-id="${row.id}" data-nombre="${escapeHTML(row.nombre_completo)}" title="Configurar Preguntas de Seguridad">
                <i class="bi bi-shield-check"></i>
              </button>
            `);
          }
        } 
        // CASO B: La fila es un usuario normal
        else {
          if (window.VEN911_PERM_EDITAR) {
            htmlActions.push(`
              <button type="button" class="btn btn-ven-edit btn-accion btn-editar me-1"
                data-id="${row.id}" data-nombre="${escapeHTML(row.nombre_completo)}"
                data-cedula="${escapeHTML(row.cedula || '')}" data-usuario="${escapeHTML(row.usuario)}"
                data-rol="${row.rol_id}" data-id-rol="${row.rol_id}" title="Editar usuario">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button type="button" class="btn btn-ven-password btn-accion btn-password"
                data-id="${row.id}" data-nombre="${escapeHTML(row.nombre_completo)}" title="Cambiar contraseña">
                <i class="bi bi-key-fill"></i>
              </button>
            `);
          }
        }

        // Si no se agregó nada (sin permisos), mostrar placeholder
        if (htmlActions.length === 0) {
          return '<span class="text-muted small italic"><i class="bi bi-lock-fill me-1"></i>Sin acceso</span>';
        }

        return htmlActions.join('');
      },
    },
  ];

  const configuracionLenguaje = window.Ven911DataTablesLang;

  // ========================
  // 2. INICIALIZAR DATATABLES
  // ========================
  const tabla = $('#tablaUsuarios').DataTable({
    autoWidth: false,
    serverSide: true,
    processing: true,
    ajax: {
      url: 'index.php?url=usuario/obtenerDatos&estado=activo',
      type: 'POST',
      error: function () {
        Swal.fire('Error', 'No se pudieron cargar los datos de usuarios.', 'error');
      },
    },
    columns: configuracionColumnas,
    language: configuracionLenguaje,
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 10,
  });

  const tablaInactivos = $('#tablaInactivos').DataTable({
    autoWidth: false,
    serverSide: true,
    processing: true,
    ajax: {
      url: 'index.php?url=usuario/obtenerDatos&estado=inactivo',
      type: 'POST',
      error: function () {
        Swal.fire('Error', 'No se pudieron cargar los datos inactivos.', 'error');
      },
    },
    columns: configuracionColumnas,
    language: { ...configuracionLenguaje, emptyTable: 'No hay usuarios inactivos.' },
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 10,
  });

  // Actualizar el badge del total cuando cargan los datos
  tabla.on('xhr.dt', function (e, settings, json) {
    const total = (json && json.recordsTotal !== undefined) ? json.recordsTotal : 0;
    $('#badge-count-total').text(`${total} usuario${total !== 1 ? 's' : ''}`);
  });

  // Actualizar badge de inactivos
  tablaInactivos.on('xhr.dt', function (e, settings, json) {
    const total = (json && json.recordsTotal !== undefined) ? json.recordsTotal : 0;
    $('#badge-count-inactivos').text(`${total} usuario${total !== 1 ? 's' : ''}`);
  });

  // Escuchar evento global de recarga
  $(document).on('usuarios:reload', function () {
    tabla.ajax.reload(null, false);
    tablaInactivos.ajax.reload(null, false);
  });

  // ========================
  // 3. HELPERS
  // ========================
  function recargarTabla() {
    $(document).trigger('usuarios:reload');
  }

  function bloquearBtn($btn, texto = 'Procesando...') {
    $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span>${texto}`);
  }
  function desbloquearBtn($btn, html) { $btn.prop('disabled', false).html(html); }

  // Toggle visibilidad de contraseña
  $(document).on('click', '.btn-eye', function () {
    const targetId = $(this).data('target');
    const $input = $('#' + targetId);
    const $icon = $(this).find('i');
    if ($input.attr('type') === 'password') {
      $input.attr('type', 'text');
      $icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
      $input.attr('type', 'password');
      $icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
  });

  // ========================
  // 4. CREAR USUARIO
  // ========================

  // Mostrar/ocultar campos de seguridad según el rol
  $('#crear-rol').on('change', function () {
    const rolId = parseInt($(this).val());
    if (rolId === 1) {
      $('#seccion-seguridad-crear').slideDown();
    } else {
      $('#seccion-seguridad-crear').slideUp();
    }
  });
  $('#formCrearUsuario').on('submit', function (e) {
    e.preventDefault();
    const $btn = $('#btn-guardar-crear');
    const $form = $(this);
    const originalHtml = $btn.html();

    bloquearBtn($btn, 'Guardando...');

    $.ajax({
      url: 'index.php?url=usuario/guardar',
      method: 'POST',
      data: $form.serialize(),
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
      .always(function () { desbloquearBtn($btn, originalHtml); });
  });

  // Limpiar form al cerrar modal
  $('#modalCrearUsuario').on('hidden.bs.modal', function () {
    $('#formCrearUsuario')[0].reset();
  });

  // ========================
  // 5. EDITAR USUARIO
  // ========================
  $(document).on('click', '.btn-editar', function () {
    const $btn = $(this);
    $('#editar-id').val($btn.data('id'));
    $('#editar-nombre').val($btn.data('nombre'));
    $('#editar-cedula').val($btn.data('cedula'));
    $('#editar-usuario').val($btn.data('usuario'));
    $('#editar-rol').val($btn.data('rol'));
    $('#modalEditarUsuario').modal('show');
  });

  $('#formEditarUsuario').on('submit', function (e) {
    e.preventDefault();
    const $btn = $('#btn-guardar-editar');
    const $form = $(this);
    const originalHtml = $btn.html();

    bloquearBtn($btn, 'Guardando...');

    $.ajax({
      url: 'index.php?url=usuario/actualizar',
      method: 'POST',
      data: $form.serialize(),
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
      .always(function () { desbloquearBtn($btn, originalHtml); });
  });

  // ========================
  // 6. CAMBIAR CONTRASEÑA
  // ========================
  $(document).on('click', '.btn-password', async function () {
    const $btn = $(this);
    const id = $btn.data('id');

    $('#pwd-id').val(id);
    $('#pwd-nombre-usuario').text($btn.data('nombre'));
    $('#pwd-nueva').val('').attr('type', 'password');
    $('#pwd-confirmar').val('').attr('type', 'password');
    $('#formCambiarPassword').find('.btn-eye i').removeClass('bi-eye-slash').addClass('bi-eye');

    // Resetear sección de seguridad
    $('#seccion-validacion-seguridad').hide();
    $('#ans-1, #ans-2').val('').prop('required', false);

    // Si es SuperAdmin (detectamos por el icono de escudo en la fila o simplemente intentamos cargar)
    // En este caso, mejor preguntamos al servidor si tiene preguntas
    try {
      const res = await $.getJSON(`index.php?url=usuario/obtenerPreguntasSeguridad&id=${id}`);
      if (res.success) {
        $('#label-pregunta-1').text(res.questions.p1_texto);
        $('#label-pregunta-2').text(res.questions.p2_texto);
        $('#ans-1, #ans-2').prop('required', true);
        $('#seccion-validacion-seguridad').slideDown();
      }
    } catch (e) { console.log("Usuario sin preguntas de seguridad o error."); }

    $('#modalCambiarPassword').modal('show');
  });

  $('#formCambiarPassword').on('submit', function (e) {
    e.preventDefault();
    const $btn = $(this).find('[type="submit"]');
    const $form = $(this);
    const originalHtml = $btn.html();

    bloquearBtn($btn, 'Actualizando...');

    $.ajax({
      url: 'index.php?url=usuario/actualizarContrasena',
      method: 'POST',
      data: $form.serialize(),
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
      .always(function () { desbloquearBtn($btn, originalHtml); });
  });

  // ========================
  // 7. TOGGLE ESTADO
  // ========================
  $(document).on('click', '.btn-toggle-estado', function () {
    const id = $(this).data('id');
    const estado = $(this).data('estado');
    const accion = estado === 'activo' ? 'desactivar' : 'activar';

    Swal.fire({
      title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} usuario?`,
      text: `El usuario pasará a estado ${accion === 'activar' ? 'activo' : 'inactivo'}.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: `Sí, ${accion}`,
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#16a34a',
      cancelButtonColor: '#f0fdf4',
    }).then(function (result) {
      if (!result.isConfirmed) return;

      $.ajax({
        url: 'index.php?url=usuario/alternarEstado',
        method: 'POST',
        data: { id: id },
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

  // ========================
  // 8. CONFIGURAR SEGURIDAD (SUPERADMIN)
  // ========================
  $(document).on('click', '.btn-config-seguridad', function () {
    const $btn = $(this);
    $('#seg-id').val($btn.data('id'));
    $('#seg-factory-code').val('');
    $('#formConfigSeguridad')[0].reset();
    // Re-establecer el ID oculto porque reset lo borra
    $('#seg-id').val($btn.data('id'));
    $('#modalConfigSeguridad').modal('show');
  });

  $('#formConfigSeguridad').on('submit', function (e) {
    e.preventDefault();
    const $btn = $(this).find('[type="submit"]');
    const $form = $(this);
    const originalHtml = $btn.html();

    bloquearBtn($btn, 'Actualizando...');

    $.ajax({
      url: 'index.php?url=usuario/actualizarPreguntasSeguridad',
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json'
    })
      .done(function (res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: '¡Actualizado!', text: res.message, timer: 2000, showConfirmButton: false });
          $('#modalConfigSeguridad').modal('hide');
        } else {
          Swal.fire({ icon: 'warning', title: 'Atención', text: res.message });
        }
      })
      .fail(function () {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación.' });
      })
      .always(function () {
        desbloquearBtn($btn, originalHtml);
      });
  });

}); // fin $(function)
