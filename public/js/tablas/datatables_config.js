/**
 * Configuración global para todas las DataTables del sistema (Ven911)
 * Evita la redundancia de código en los inicializadores de tablas (Inercia Cero).
 */

// 1. Sanitizador global de cadenas HTML (Cross-Site Scripting Protection)
window.escapeHTML = (str) => {
    if (typeof str !== 'string' && str != null) str = str.toString();
    if (!str) return str;
    return str.replace(/[&<>'"]/g,
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
};

// 2. Traducción unificada de la interfaz de DataTables a Español
window.Ven911DataTablesLang = {
    decimal:          ',',
    emptyTable:       'No hay registros disponibles en la tabla.',
    info:             'Mostrando _START_ a _END_ de _TOTAL_ registros',
    infoEmpty:        'Mostrando 0 a 0 de 0 registros',
    infoFiltered:     '(filtrado de _MAX_ registros totales)',
    infoPostFix:      '',
    thousands:        '.',
    lengthMenu:       'Mostrar _MENU_ registros',
    loadingRecords:   'Cargando registros...',
    processing:       '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Procesando...</span></div>',
    search:           'Buscar:',
    zeroRecords:      'No se encontraron coincidencias.',
    paginate: {
        first:    '«',
        last:     '»',
        next:     '›',
        previous: '‹',
    },
    aria: {
        sortAscending:  ': Activar para ordenar la columna descendente',
        sortDescending: ': Activar para ordenar la columna ascendente'
    }
};
