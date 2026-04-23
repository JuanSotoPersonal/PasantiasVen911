/**
 * usuarios_datatable.js - Gestión Integral de Usuarios y RBAC
 * 
 * Centraliza la administración de cuentas, roles y seguridad. Implementa
 * protección de nivel SuperAdmin, gestión de estados (Activo/Inactivo),
 * control de preguntas de seguridad y ruteo de permisos dinámicos en la UI.
 */

$(function () {

  // 1. CONFIGURACIÓN INICIAL Y MIXINS (SWEETALERT)
  // Se pre-configura Swal para evitar saltos de layout en el body
  window.Swal = Swal.mixin({
    heightAuto: false,
    scrollbarPadding: false
  });

  // 2. DEFINICIÓN DE COLUMNAS Y RENDERERS (RBAC CORE)
  const configuracionColumnas = [
    { 
        // Columna: Índice correlativo
        data: null, width: '50px', orderable: false, searchable: false, 
        render: (d, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 
    },
    { data: 'nombre_completo', render: (d) => escapeHTML(d) },
    { data: 'usuario', render: (d) => escapeHTML(d) },
    {
      data: 'cedula',
      render: (d) => d ? `V-${escapeHTML(d)}` : '<span class="text-muted fst-italic small">Sin cédula</span>',
    },
    { data: 'nombre_rol', render: (d) => escapeHTML(d) },
    {
      // Columna: Estado (Badge interactivo con protección de SuperAdmin)
      data: 'estado',
      render: (d, type, row) => {
        const isActivo = d === 'activo';
        const badgeClass = isActivo ? 'badge-activo' : 'badge-inactivo';
        const icon = isActivo ? 'bi-toggle-on' : 'bi-toggle-off';

        // Bypass de seguridad: El SuperAdmin (Rol 1) es siempre activo y estático
        if (row.rol_id == 1) {
          return `
          <h3>
            <span class="badge badge-estado ${badgeClass}">
              <i class="bi bi-shield-lock-fill me-1"></i>Activo
            </span>
          </h3>`;
        }

        // Restricción por permisos de edición (Vista de lectura)
        if (!window.VEN911_PERM_EDITAR) {
          return `
          <h3>
            <span class="badge badge-estado ${badgeClass}">
              <i class="bi ${isActivo ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i>${isActivo ? 'Activo' : 'Inactivo'}
            </span>
          </h3>`;
        }

        // Renderizado del botón interactivo para cambio de estado
        return `
          <button type="button" class="btn-toggle-estado" data-id="${row.id}" data-estado="${d}"
            title="Clic para cambiar estado">
            <span class="badge badge-estado ${badgeClass}">
              <i class="bi ${icon} me-1"></i>${isActivo ? 'Activo' : 'Inactivo'}
            </span>
          </button>`;
      },
    },
    {
      // Columna: Acciones dinámicas según Rol y Permisos
      data: null,
      orderable: false,
      searchable: false,
      className: 'text-center',
      render: (d, type, row) => {
        const htmlActions = [];

        // CASO A: Fila es Administrador (Protección de identidad)
        if (row.rol_id == 1) {
          htmlActions.push(`
            <span class="btn-ven-edit btn-accion me-1 d-inline-flex align-items-center justify-content-center"
                  style="cursor: help; opacity: 0.9;" title="Administrador Protegido">
              <i class="bi bi-shield-lock-fill"></i>
            </span>
          `);

          // Solo el SuperAdmin puede gestionar la seguridad de otro SuperAdmin
          if (window.USER_ROL_ID === 1) {
            htmlActions.push(`
              <button type="button" class="btn btn-ven-password btn-accion btn-password"
                data-id="${row.id}" data-nombre="${escapeHTML(row.nombre_completo)}" title="Cambiar contraseña">
                <i class="bi bi-key-fill"></i>
              </button>
              <button type="button" class="btn btn-ven-primary btn-accion btn-config-seguridad"
                data-id="${row.id}" data-nombre="${escapeHTML(row.nombre_completo)}" title="Configurar Preguntas de Seguridad">
                <i class="bi bi-shield-check"></i>
              </button>
            `);
          }
        } 
        // CASO B: Usuario estándar (Gestión operativa)
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

        if (htmlActions.length === 0) {
          return '<span class="text-muted small italic"><i class="bi bi-lock-fill me-1"></i>Sin acceso</span>';
        }

        return htmlActions.join('');
      },
    },
  ];

  const configuracionLenguaje = window.Ven911DataTablesLang;

  // 3. INICIALIZACIÓN DE DATATABLES (SERVER-SIDE PROCESSING)
  const tabla = $('#tablaUsuarios').DataTable({
    autoWidth: false,
    serverSide: true,
    processing: true,
    ajax: {
      url: 'index.php?url=usuario/obtenerDatos&estado=activo',
      type: 'POST',
      error: function () {
        Swal.fire('Error', 'No se pudieron cargar los datos de usuarios activos.', 'error');
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

  // Gestión de contadores en tiempo real tras carga de datos
  tabla.on('xhr.dt', function (e, settings, json) {
    const total = (json && json.recordsTotal !== undefined) ? json.recordsTotal : 0;
    $('#badge-count-total').text(`${total} usuario${total !== 1 ? 's' : ''}`);
  });

  tablaInactivos.on('xhr.dt', function (e, settings, json) {
    const total = (json && json.recordsTotal !== undefined) ? json.recordsTotal : 0;
    $('#badge-count-inactivos').text(`${total} usuario${total !== 1 ? 's' : ''}`);
  });

  // 4. HELPERS Y UTILIDADES
  function recargarTabla() {
    tabla.ajax.reload(null, false);
    tablaInactivos.ajax.reload(null, false);
  }

  function bloquearBtn($btn, texto = 'Procesando...') {
    $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span>${texto}`);
  }
  function desbloquearBtn($btn, html) { $btn.prop('disabled', false).html(html); }

  // Toggle de visibilidad de contraseñas
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

  // 5. MÓDULO: CREACIÓN DE USUARIOS
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

  $('#modalCrearUsuario').on('hidden.bs.modal', function () {
    $('#formCrearUsuario')[0].reset();
  });

  // 6. MÓDULO: EDICIÓN DE PERFILES
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

  // 7. MÓDULO: SEGURIDAD (PASSWORD & PREGUNTAS)
  $(document).on('click', '.btn-password', async function () {
    const $btn = $(this);
    const id = $btn.data('id');

    $('#pwd-id').val(id);
    $('#pwd-nombre-usuario').text($btn.data('nombre'));
    $('#pwd-nueva, #pwd-confirmar').val('').attr('type', 'password');
    $('#formCambiarPassword').find('.btn-eye i').removeClass('bi-eye-slash').addClass('bi-eye');

    $('#seccion-validacion-seguridad').hide();
    $('#ans-1, #ans-2').val('').prop('required', false);

    // Verificación de preguntas de seguridad para cuentas protegidas
    try {
      const res = await $.getJSON(`index.php?url=usuario/obtenerPreguntasSeguridad&id=${id}`);
      if (res.success) {
        $('#label-pregunta-1').text(res.questions.p1_texto);
        $('#label-pregunta-2').text(res.questions.p2_texto);
        $('#ans-1, #ans-2').prop('required', true);
        $('#seccion-validacion-seguridad').slideDown();
      }
    } catch (e) { /* Usuario sin preguntas */ }

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
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación.' });
      })
      .always(function () { desbloquearBtn($btn, originalHtml); });
  });

  // Configuración de preguntas de seguridad (Exclusivo SuperAdmin)
  $(document).on('click', '.btn-config-seguridad', function () {
    const $btn = $(this);
    $('#seg-id').val($btn.data('id'));
    $('#formConfigSeguridad')[0].reset();
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
      .always(function () { desbloquearBtn($btn, originalHtml); });
  });

  // 8. MÓDULO: ESTADOS (TOGGLE ACTIVO/INACTIVO)
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
          Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación.' });
        });
    });
  });

});
