// usuarios/datatable.js
// Módulo Usuarios: DataTable, CRUD (crear, editar, contraseña, toggle estado)
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
        render: (d) => d ? `V-${d}` : '<span class="text-muted fst-italic small">Sin cédula</span>',
      },
      { data: 'nombre_rol' },
      {
        data: 'codigo_operador',
        render: (d) => d ? `<code>${d}</code>` : '<span class="text-muted fst-italic small">—</span>',
      },
      {
        data: 'estado',
        render: (d, type, row) => {
          const isActivo   = d === 'activo';
          const badgeClass = isActivo ? 'badge-activo' : 'badge-inactivo';
          const icon       = isActivo ? 'bi-toggle-on' : 'bi-toggle-off';
          if (row.rol_id === 1) {
            return `
            <h3>
              <span class="badge badge-estado ${badgeClass}">
                <i class="bi bi-shield-lock-fill me-1"></i>Activo
              </span>
            </h3>`;
          }
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
      url: '',
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

  // Actualizar el badge de total cuando cargan los datos
  tabla.on('xhr.dt', function (e, settings, json) {
    const total = (json && json.data) ? json.data.length : 0;
    $('#badge-count-total').text(`${total} usuario${total !== 1 ? 's' : ''}`);
  });

  // Escuchar evento global de recarga
  $(document).on('usuarios:reload', function () {
    tabla.ajax.reload(null, false);
  });

  // ========================
  // 2. HELPERS
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
    const $btn         = $('#btn-guardar-crear');
    const $form        = $(this);
    const originalHtml = $btn.html();

    bloquearBtn($btn, 'Guardando...');

    $.ajax({
      url:      'index.php?url=usuario/store',
      method:   'POST',
      data:     $form.serialize(),
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
    const $btn         = $('#btn-guardar-editar');
    const $form        = $(this);
    const originalHtml = $btn.html();

    bloquearBtn($btn, 'Guardando...');

    $.ajax({
      url:      'index.php?url=usuario/update',
      method:   'POST',
      data:     $form.serialize(),
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
    const $btn         = $(this).find('[type="submit"]');
    const $form        = $(this);
    const originalHtml = $btn.html();

    bloquearBtn($btn, 'Actualizando...');

    $.ajax({
      url:      'index.php?url=usuario/updatePassword',
      method:   'POST',
      data:     $form.serialize(),
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
  // 6. TOGGLE ESTADO
  // ========================
  $(document).on('click', '.btn-toggle-estado', function () {
    const id     = $(this).data('id');
    const estado = $(this).data('estado');
    const accion = estado === 'activo' ? 'desactivar' : 'activar';

    Swal.fire({
      title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} usuario?`,
      text:  `El usuario pasará a estado ${accion === 'activar' ? 'activo' : 'inactivo'}.`,
      icon:  'question',
      showCancelButton:   true,
      confirmButtonText:  `Sí, ${accion}`,
      cancelButtonText:   'Cancelar',
      confirmButtonColor: '#16a34a',
      cancelButtonColor:  '#f0fdf4',
    }).then(function (result) {
      if (!result.isConfirmed) return;

      $.ajax({
        url:      'index.php?url=usuario/toggleEstado',
        method:   'POST',
        data:     { id: id },
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
