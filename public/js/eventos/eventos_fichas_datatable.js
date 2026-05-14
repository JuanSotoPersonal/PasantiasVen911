/**
 * eventos_fichas_datatable.js - Trazabilidad Operativa de Fichas (Ven911)
 * 
 * Gestiona la visualización del flujo de vida de las fichas de emergencia.
 * Muestra cambios de estado, despachos y cierres, permitiendo auditar la 
 * evolución de cada incidente mediante procesamiento en servidor y visualización JSON.
 */

$(document).ready(function () {
    
    // 1. INICIALIZACIÓN DE DATATABLE DE TRAZABILIDAD (SERVER-SIDE)
    const tablaEventosFichas = $('#tablaEventosFichas').DataTable({
        "serverSide": true,
        "processing": true,
        "ajax": {
            "url":    "index.php?url=evento/obtenerDatosFichas",
            "type":   "POST"
        },
        "columns": [
            { 
                // Columna: Tipo de Evento Operativo
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
                // Columna: Identificador de la Ficha
                "data": "ficha_id",
                "render": function(data) {
                    return `<span class="fw-bold">#${data}</span>`;
                }
            },
            { 
                // Columna: Estado de origen
                "data": "estado_anterior",
                "defaultContent": '<small class="text-muted fst-italic">Inicio</small>',
                "render": function(data) {
                    if (!data) return '<small class="text-muted fst-italic">Inicio</small>';
                    return `<span class="badge bg-light text-dark border">${data}</span>`;
                }
            },
            { 
                // Columna: Estado de destino
                "data": "estado_nuevo",
                "render": function(data) {
                    return `<span class="badge bg-light text-dark border">${data}</span>`;
                }
            },
            { 
                // Columna: Operador/Despachador responsable
                "data": "nombre_admin", 
                "defaultContent": "<i>Desconocido</i>", 
                "render": function(data) { 
                    if (!data) return "<i>Desconocido</i>";
                    return escapeHTML(data); 
                }
            },
            { 
                // Columna: Fecha y Hora del movimiento
                "data": "fecha" 
            },
            {
                // Columna: Acciones (Detalles técnicos del cambio)
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
        "language": window.Ven911DataTablesLang, // Traducción global
        "order": [[5, "desc"]], // Ordenar por fecha más reciente
        "responsive": true,
        "searchDelay": 600 // Debounce para optimizar búsquedas
    });

    // 2. GESTIÓN DEL MODAL DE CAMBIOS (DELEGACIÓN DE EVENTOS)
    $('#tablaEventosFichas').on('click', '.btn-ver-detalles', function () {
        const anterior = $(this).attr('data-anterior');
        const nuevo    = $(this).attr('data-nuevo');
        const detalles = $(this).attr('data-detalles');

        // Renderizado dinámico de comparativa JSON
        mostrarJSON('#contentValorAnterior', anterior);
        mostrarJSON('#contentValorNuevo', nuevo);
        
        // Inyección de descripción operativa
        $('#contentDetalles').text(detalles);

        // Apertura del modal de auditoría
        const modal = new bootstrap.Modal(document.getElementById('modalDetallesEvento'));
        modal.show();
    });

    // 3. HELPERS DE PROCESAMIENTO Y RENDERIZADO
    /**
     * Formatea datos JSON de forma visualmente estructurada.
     * Incluye manejo de fallbacks para datos planos o nulos.
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
            const pre = $('<pre class="mb-0 text-dark" style="font-size: 0.85rem;"><code></code></pre>');
            pre.find('code').text(formatted);
            container.append(pre);
        } catch (e) {
            // Renderizado seguro en caso de no ser un JSON válido
            const span = $('<span class="text-dark"></span>').text(data);
            container.append(span);
        }
    }
});
