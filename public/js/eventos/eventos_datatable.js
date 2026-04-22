
$(document).ready(function () {
    const tablaEventos = $('#tablaEventos').DataTable({
        "serverSide": true,
        "processing": true,
        "ajax": {
            "url":    "index.php?url=evento/obtenerDatos",
            "type":   "POST"
        },
        "columns": [
            { 
                "data": "tipo_accion",
                "render": function(data, type, row) {
                    // Fallback para nombres de campo antiguos si tipo_accion es undefined
                    const accion = data || row.accion || "S/D";
                    
                    let badgeClass = 'bg-secondary';
                    if (accion === 'INSERT') badgeClass = 'bg-success';
                    if (accion === 'UPDATE') badgeClass = 'bg-warning text-dark';
                    if (accion === 'DELETE') badgeClass = 'bg-danger';
                    if (accion === 'LOGIN') badgeClass = 'bg-info text-dark';
                    if (accion === 'LOGOUT') badgeClass = 'bg-primary text-white';
                    if (accion === 'CAMBIO_ESTADO') badgeClass = 'bg-dark text-white';
                    
                    return `<span class="badge ${badgeClass}">${accion}</span>`;
                }
            },
            { "data": "tabla_afectada", "render": function(data) { return escapeHTML(data); } },
            { "data": "registro_id" },
            { "data": "nombre_admin", "defaultContent": "<i>Desconocido</i>", "render": function(data, type, row) { 
                if (!data) return "<i>Desconocido</i>";
                return escapeHTML(data); 
            }},
            { "data": "fecha" },
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                "className": "text-center",
                "render": function (data, type, row) {
                    // Soporte para descripcion o detalles (fallback)
                    const desc = row.descripcion || row.detalles || 'Sin descripción';
                    
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
        "order": [[4, "desc"]],
        "responsive": true
    });

    // Delegación de eventos para el botón "Ver Detalles"
    $('#tablaEventos').on('click', '.btn-ver-detalles', function () {
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
            container.append(span); // Si no es JSON, mostrar como texto plano de forma segura
        }
    }
});
