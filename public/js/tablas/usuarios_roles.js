// usuarios/roles.js
// Inicializa un DataTable individual por cada tabla de rol (data-rol-id)

$(function () {

  const lang = window.Ven911DataTablesLang;

  // Inicializar un DataTable por cada tabla con data-rol-id
  $('table[data-rol-id]').each(function () {
    const $tabla    = $(this);
    const rolId     = $tabla.data('rol-id');
    const rolNombre = $tabla.data('rol-nombre');

    const columnas = [
      {
        data: null,
        width: '50px',
        orderable: false,
        searchable: false,
        render: (d, type, row, meta) => meta.row + 1,
      },
      { data: 'nombre_completo', render: (d) => escapeHTML(d) },
      { data: 'usuario',         render: (d) => escapeHTML(d) },
      {
        data: 'cedula',
        render: (d) => d ? `V-${escapeHTML(d)}` : '<span class="text-muted fst-italic small">Sin cédula</span>',
      },
      {
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

    const dt = $tabla.DataTable({
      autoWidth:  false,
      serverSide: true,
      processing: true,
      ajax: {
        url:  `index.php?url=usuario/obtenerDatosPorRol&rol_id=${rolId}&estado=activo`,
        type: 'POST',
        error: function () {
          Swal.fire('Error', `No se pudo cargar la tabla del rol ${rolNombre}.`, 'error');
        },
      },
      columns:    columnas,
      language:   lang,
      responsive: true,
      order:      [[1, 'asc']],
      pageLength: 10,
    });

    // Actualizar badge usando recordsTotal del JSON server-side
    $tabla.on('xhr.dt', function (e, settings, json) {
      const total = (json && json.recordsTotal !== undefined) ? json.recordsTotal : 0;
      $(`#badge-count-${rolId}`).text(`${total} usuario${total !== 1 ? 's' : ''}`);
    });

    // Escuchar evento global de recarga
    $(document).on('usuarios:reload', function () {
      dt.ajax.reload(null, false);
    });
  });

}); // fin $(function)

