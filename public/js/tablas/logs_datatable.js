$(document).ready(function () {
    const tablaLogs = $('#tablaLogs').DataTable({
        "ajax": {
            "url": "index.php?url=log/getData",
            "dataSrc": "data"
        },
        "columns": [
            { 
                "data": "accion",
                "render": function(data) {
                    let badgeClass = 'bg-secondary';
                    if (data === 'INSERT') badgeClass = 'bg-success';
                    if (data === 'UPDATE') badgeClass = 'bg-warning text-dark';
                    if (data === 'DELETE') badgeClass = 'bg-danger';
                    if (data === 'LOGIN') badgeClass = 'bg-info text-dark';
                    if (data === 'LOGOUT') badgeClass = 'bg-primary text-white';
                    if (data === 'CAMBIO_ESTADO') badgeClass = 'bg-dark text-white';
                    
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { "data": "tabla_afectada" },
            { "data": "registro_id" },
            { "data": "nombre_admin", "defaultContent": "<i>Desconocido</i>" },
            { "data": "fecha" },
            {
                "data": null,
                "className": "text-center",
                "render": function (data, type, row) {
                    return `
                        <button class="btn btn-ven-primary btn-sm btn-ver-detalles" 
                                data-anterior='${row.valor_anterior || ""}' 
                                data-nuevo='${row.valor_nuevo || ""}' 
                                data-detalles="${row.detalles || 'Sin descripción'}">
                            <i class="bi bi-eye"></i> Ver Cambios
                        </button>
                    `;
                }
            }
        ],
        "language": {
            "decimal":        ",",
            "emptyTable":     "No hay registros de historial.",
            "info":           "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty":      "Sin registros disponibles",
            "infoFiltered":   "(filtrado de _MAX_ registros totales)",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No se encontraron coincidencias.",
            "paginate": {
                "first":    "«",
                "last":     "»",
                "next":     "›",
                "previous": "‹",
            }
        },
        "order": [[4, "desc"]],
        "responsive": true
    });

    // Delegación de eventos para el botón "Ver Detalles"
    $('#tablaLogs').on('click', '.btn-ver-detalles', function () {
        const anterior = $(this).attr('data-anterior');
        const nuevo = $(this).attr('data-nuevo');
        const detalles = $(this).attr('data-detalles');

        mostrarJSON('#contentValorAnterior', anterior);
        mostrarJSON('#contentValorNuevo', nuevo);
        $('#contentDetalles').text(detalles);

        const modal = new bootstrap.Modal(document.getElementById('modalDetallesLog'));
        modal.show();
    });

    /**
     * Formatea y muestra un string JSON en un contenedor específico
     */
    function mostrarJSON(selector, data) {
        const container = $(selector);
        container.empty();

        if (!data || data === "" || data === "null") {
            container.html('<span class="text-muted fst-italic">Sin datos previos</span>');
            return;
        }

        try {
            const obj = JSON.parse(data);
            const formatted = JSON.stringify(obj, null, 2);
            container.html(`<pre class="mb-0 text-dark" style="font-size: 0.85rem;"><code>${formatted}</code></pre>`);
        } catch (e) {
            container.html(`<span class="text-dark">${data}</span>`); // Si no es JSON, mostrar como texto plano
        }
    }
});
