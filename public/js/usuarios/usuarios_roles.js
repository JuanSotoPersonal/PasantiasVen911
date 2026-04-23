/**
 * usuarios_roles.js - Gestión Segmentada de Usuarios por Rol
 * 
 * Este módulo automatiza la inicialización de múltiples DataTables basándose
 * en el atributo 'data-rol-id' de las tablas presentes en la vista. Permite
 * una visualización organizada por jerarquías (Administradores, Operadores, etc.)
 * con procesamiento independiente en servidor.
 */

$(function () {

  // 1. CONFIGURACIÓN GLOBAL
  const lang = window.Ven911DataTablesLang;

  // 2. INICIALIZACIÓN DINÁMICA DE TABLAS POR ROL
  // Se itera sobre cada tabla que posea el atributo data-rol-id para instanciar su DataTable
  $('table[data-rol-id]').each(function () {
    const $tabla    = $(this);
    const rolId     = $tabla.data('rol-id');
    const rolNombre = $tabla.data('rol-nombre');

    // Definición de estructura de columnas compartida
    const columnas = [
      {
        // Columna: Índice correlativo
        data: null,
        width: '50px',
        orderable: false,
        searchable: false,
        render: (d, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1,
      },
      { data: 'nombre_completo', render: (d) => escapeHTML(d) },
      { data: 'usuario',         render: (d) => escapeHTML(d) },
      {
        // Columna: Cédula con formato institucional
        data: 'cedula',
        render: (d) => d ? `V-${escapeHTML(d)}` : '<span class="text-muted fst-italic small">Sin cédula</span>',
      },
      {
        // Columna: Estado (Badge interactivo de Toggle)
        data: 'estado',
        render: (d, type, row) => {
          const activo     = d === 'activo';
          const badgeClass = activo ? 'badge-activo' : 'badge-inactivo';
          const icon       = activo ? 'bi-toggle-on' : 'bi-toggle-off';
          return `
            <button
              type="button"
              class="btn-toggle-estado"
              data-id="${row.id}"
              data-estado="${d}"
              title="Clic para cambiar estado"
            >
              <span class="badge badge-estado ${badgeClass}">
                <i class="bi ${icon} me-1"></i>${activo ? 'Activo' : 'Inactivo'}
              </span>
            </button>`;
        },
      },
      {
        // Columna: Acciones (Edición y Seguridad)
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: (d, type, row) => `
          <button
            type="button"
            class="btn btn-ven-edit btn-accion btn-editar me-1"
            data-id="${row.id}"
            data-nombre="${escapeHTML(row.nombre_completo)}"
            data-cedula="${escapeHTML(row.cedula || '')}"
            data-usuario="${escapeHTML(row.usuario)}"
            data-rol="${row.rol_id}"
            data-id-rol="${row.rol_id}"
            title="Editar usuario"
          >
            <i class="bi bi-pencil-fill"></i>
          </button>
          <button
            type="button"
            class="btn btn-ven-password btn-accion btn-password"
            data-id="${row.id}"
            data-nombre="${escapeHTML(row.nombre_completo)}"
            title="Cambiar contraseña"
          >
            <i class="bi bi-key-fill"></i>
          </button>`,
      },
    ];

    // 3. INSTANCIACIÓN DE DATATABLE (SERVER-SIDE)
    const dt = $tabla.DataTable({
      autoWidth:  false,
      serverSide: true,
      processing: true,
      ajax: {
        // Ruteo dinámico basado en el ID del rol de la tabla actual
        url:  `index.php?url=usuario/obtenerDatosPorRol&rol_id=${rolId}&estado=activo`,
        type: 'POST',
        error: function () {
          Swal.fire('Error', `No se pudo cargar la tabla del rol ${rolNombre}.`, 'error');
        },
      },
      columns:    columnas,
      language:   lang,
      responsive: true,
      order:      [[1, 'asc']], // Ordenar por nombre completo
      pageLength: 10,
    });

    // Sincronización de badges de conteo (Total de registros del servidor)
    $tabla.on('xhr.dt', function (e, settings, json) {
      const total = (json && json.recordsTotal !== undefined) ? json.recordsTotal : 0;
      $(`#badge-count-${rolId}`).text(`${total} usuario${total !== 1 ? 's' : ''}`);
    });

    // Escucha del evento global de recarga del módulo de usuarios
    $(document).on('usuarios:reload', function () {
      dt.ajax.reload(null, false);
    });
  });

}); 
