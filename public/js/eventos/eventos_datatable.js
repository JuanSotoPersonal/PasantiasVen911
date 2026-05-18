/**
 * eventos_datatable.js - Gestión de Auditoría del Sistema (Logs)
 * 
 * Implementa la visualización de eventos de auditoría mediante DataTables con
 * procesamiento en servidor. Incluye lógica de renderizado de insignias (badges)
 * según el tipo de acción y un visor de cambios (Diff) en formato JSON.
 */

$(document).ready(function () {

    // 1. INICIALIZACIÓN DE DATATABLE (PROCESAMIENTO EN SERVIDOR)
    const tablaEventos = $('#tablaEventos').DataTable({
        "serverSide": true,
        "processing": true,
        "ajax": {
            "url": "index.php?url=evento/obtenerDatos",
            "type": "POST"
        },
        "columns": [
            {
                // Columna: Tipo de Acción (Badge dinámico)
                "data": "tipo_accion",
                "render": function (data, type, row) {
                    // Soporte para compatibilidad con esquemas de datos antiguos
                    const accion = data || row.accion || "S/D";

                    let badgeClass = 'bg-secondary';
                    if (accion === 'INSERT') badgeClass = 'bg-success';
                    if (accion === 'UPDATE') badgeClass = 'bg-warning text-dark ';
                    if (accion === 'DELETE') badgeClass = 'bg-danger';
                    if (accion === 'LOGIN') badgeClass = 'bg-info text-dark';
                    if (accion === 'LOGOUT') badgeClass = 'bg-primary text-white';
                    if (accion === 'CAMBIO_ESTADO') badgeClass = 'bg-dark text-white';

                    return `<span class="badge ${badgeClass}">${accion}</span>`;
                }
            },
            {
                // Columna: Entidad/Tabla afectada
                "data": "tabla_afectada",
                "render": function (data) { return escapeHTML(data); }
            },
            {
                // Columna: ID del registro afectado
                "data": "registro_id"
            },
            {
                // Columna: Administrador responsable
                "data": "nombre_admin",
                "defaultContent": "<i>Desconocido</i>",
                "render": function (data, type, row) {
                    if (!data) return "<i>Desconocido</i>";
                    return escapeHTML(data);
                }
            },
            {
                // Columna: Marca de tiempo
                "data": "fecha"
            },
            {
                // Columna: Acciones (Ver detalles del evento)
                "data": null,
                "orderable": false,
                "searchable": false,
                "className": "text-center",
                "render": function (data, type, row) {
                    // Fallback para campos de descripción
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
        "language": window.Ven911DataTablesLang, // Configuración de idioma global
        "order": [[4, "desc"]], // Ordenar por fecha descendente por defecto
        "responsive": true,
        "searchDelay": 600 // Debounce para optimizar búsquedas
    });

    // 2. GESTIÓN DEL MODAL DE DETALLES (DELEGACIÓN DE EVENTOS)
    $('#tablaEventos').on('click', '.btn-ver-detalles', function () {
        const anterior = $(this).attr('data-anterior');
        const nuevo = $(this).attr('data-nuevo');
        const detalles = $(this).attr('data-detalles');

        // Renderizado de valores anterior/nuevo con formateo JSON
        mostrarJSON('#contentValorAnterior', anterior);
        mostrarJSON('#contentValorNuevo', nuevo);

        // Inyección de descripción adicional
        $('#contentDetalles').text(detalles);

        // Disparo manual del modal de Bootstrap 5
        const modal = new bootstrap.Modal(document.getElementById('modalDetallesEvento'));
        modal.show();
    });

    // 3. HELPERS DE RENDERIZADO Y FORMATEO
    /**
     * Procesa y muestra datos JSON de forma legible en el contenedor especificado.
     * Si no es un JSON válido, lo renderiza como texto plano seguro.
     */
    function mostrarJSON(selector, data) {
        const container = $(selector);
        container.empty();

        // Manejo de estados nulos o vacíos
        if (!data || data === "" || data === "null") {
            container.html('<span class="text-muted fst-italic">Sin datos registrados</span>');
            return;
        }

        try {
            // Intento de parseo y formateo (pretty print)
            const obj = JSON.parse(data);
            const formatted = JSON.stringify(obj, null, 2);
            const pre = $('<pre class="mb-0 text-dark" style="font-size: 0.85rem;"><code></code></pre>');
            pre.find('code').text(formatted);
            container.append(pre);
        } catch (e) {
            // Fallback: Mostrar como texto plano seguro si falla el parseo
            const span = $('<span class="text-dark"></span>').text(data);
            container.append(span);
        }
    }
});
