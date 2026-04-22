
$(document).ready(function () {
    const tablaEventosFichas = $('#tablaEventosFichas').DataTable({
        "serverSide": true,
        "processing": true,
        "ajax": {
            "url":    "index.php?url=evento/obtenerDatosFichas",
            "type":   "POST"
        },
        "columns": [
            { 
                "data": "tipo_evento",
                "render": function(data) {
                    let badgeClass = 'bg-secondary';
                    if (data === 'CREACION') badgeClass = 'bg-success';
                    if (data === 'MODIFICACION') badgeClass = 'bg-warning text-dark';
                    if (data === 'DESPACHO') badgeClass = 'bg-info text-dark';
                    if (data === 'CIERRE') badgeClass = 'bg-dark text-white';
                    if (data === 'CAMBIO_ESTADO') badgeClass = 'bg-primary';
                    
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { 
                "data": "ficha_id",
                "render": function(data) {
                    return `<span class="fw-bold">#${data}</span>`;
                }
            },
            { 
                "data": "estado_anterior",
                "defaultContent": '<small class="text-muted fst-italic">Inicio</small>',
                "render": function(data) {
                    if (!data) return '<small class="text-muted fst-italic">Inicio</small>';
                    return `<span class="badge bg-light text-dark border">${data}</span>`;
                }
            },
            { 
                "data": "estado_nuevo",
                "render": function(data) {
                    return `<span class="badge bg-light text-dark border">${data}</span>`;
                }
            },
            { 
                "data": "nombre_admin", 
                "defaultContent": "<i>Desconocido</i>", 
                "render": function(data) { 
                    if (!data) return "<i>Desconocido</i>";
                    return escapeHTML(data); 
                }
            },
            { "data": "fecha" },
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                "className": "text-center",
                "render": function (data, type, row) {
                    const desc = row.descripcion || 'Sin descripción';
                    return `
                        <button class="btn btn-ven-primary btn-sm btn-ver-detalles" 
                                data-anterior='${escapeHTML(row.valor_anterior || "")}' 
                                data-nuevo='${escapeHTML(row.valor_nuevo || "")}' 
                                data-detalles="${escapeHTML(desc)}">
                            <i class="bi bi-eye"></i> Ver Cambios
                        </button>
                    `;
                }
            }
        ],
        "language": window.Ven911DataTablesLang,
        "order": [[5, "desc"]],
        "responsive": true
    });

    // Delegación de eventos para el botón "Ver Detalles" (usa la misma lógica de los otros eventos)
    $('#tablaEventosFichas').on('click', '.btn-ver-detalles', function () {
        const anterior = $(this).attr('data-anterior');
        const nuevo = $(this).attr('data-nuevo');
        const detalles = $(this).attr('data-detalles');

        mostrarJSON('#contentValorAnterior', anterior);
        mostrarJSON('#contentValorNuevo', nuevo);
        $('#contentDetalles').text(detalles);

        const modal = new bootstrap.Modal(document.getElementById('modalDetallesEvento'));
        modal.show();
    });

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
            const pre = $('<pre class="mb-0 text-dark" style="font-size: 0.85rem;"><code></code></pre>');
            pre.find('code').text(formatted);
            container.append(pre);
        } catch (e) {
            const span = $('<span class="text-dark"></span>').text(data);
            container.append(span);
        }
    }
});
