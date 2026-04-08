// usuarios_roles.js
// Inicializa un DataTable individual por cada tabla de rol (data-rol-id)

$(function () {

  // Idioma compartido con el DataTable principal
  const lang = {
    decimal:        ',',
    emptyTable:     'Sin usuarios registrados para este rol.',
    info:           'Mostrando _START_ a _END_ de _TOTAL_ usuarios',
    infoEmpty:      'Sin registros disponibles',
    infoFiltered:   '(filtrado de _MAX_ registros totales)',
    lengthMenu:     'Mostrar _MENU_ registros',
    loadingRecords: 'Cargando...',
    processing:     'Procesando...',
    search:         'Buscar:',
    zeroRecords:    'Sin coincidencias.',
    paginate: { first: '«', last: '»', next: '›', previous: '‹' },
  };

  // Inicializar un DataTable por cada tabla con data-rol-id
  $('table[data-rol-id]').each(function () {
    const $tabla  = $(this);
    const rolId   = $tabla.data('rol-id');
    const tablaId = $tabla.attr('id');

    const dt = $tabla.DataTable({
      ajax: {
        url: `index.php?url=usuario/getDataByRol&rol_id=${rolId}`,
        dataSrc: 'data',
        error: function () {
          Swal.fire('Error', `No se pudo cargar la tabla del rol ${rolId}.`, 'error');
        },
      },
      columns: [
        {
          data: null,
          width: '50px',
          orderable: false,
          searchable: false,
          render: (d, type, row, meta) => meta.row + 1,
        },
        { data: 'nombre_completo' },
        { data: 'usuario' },
        {
          data: 'cedula',
          render: (d) => d ? `V-${d}` : '<span class="text-muted fst-italic small">Sin cédula</span>',
        },
        {
          data: 'codigo_operador',
          render: (d) => d ? `<code>${d}</code>` : '<span class="text-muted fst-italic small">—</span>',
        },
        {
          data: 'estado',
          render: (d) => {
            const activo     = d === 'activo';
            const badgeClass = activo ? 'badge-activo' : 'badge-inactivo';
            const icon       = activo ? 'bi-toggle-on' : 'bi-toggle-off';
            return `<span class="badge badge-estado ${badgeClass}">
                      <i class="bi ${icon} me-1"></i>${activo ? 'Activo' : 'Inactivo'}
                    </span>`;
          },
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'text-center',
          render: (d, type, row) => {
            // Nota: En estas tablas no habrá Super Admins (ID 1) por filtro de backend,
            // pero mantenemos la lógica por consistencia.
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
      language: lang,
      responsive: true,
      order: [[0, 'asc']],
      pageLength: 10,
    });

    // Actualizar badge de conteo cuando los datos carguen
    $tabla.on('xhr.dt', function (e, settings, json) {
      const total = (json && json.data) ? json.data.length : 0;
      $(`#badge-count-${rolId}`).text(`${total} usuario${total !== 1 ? 's' : ''}`);
    });
  });

}); // fin $(function)
