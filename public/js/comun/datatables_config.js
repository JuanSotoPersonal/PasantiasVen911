/**
 * datatables_config.js - Configuración Centralizada de DataTables (Ven911)
 * 
 * Centraliza las utilidades de seguridad (XSS) y la internacionalización para
 * asegurar un comportamiento consistente en todas las tablas del sistema,
 * eliminando la redundancia de código y facilitando el mantenimiento global.
 */

// 1. SEGURIDAD: SANITIZADOR DE CADENAS HTML (Protección XSS)
window.escapeHTML = (str) => {
    // Conversión forzada a cadena si el valor no es nulo
    if (typeof str !== 'string' && str != null) str = str.toString();
    
    // Si la cadena está vacía o es nula, se retorna tal cual
    if (!str) return str;

    // Mapeo de caracteres especiales a entidades HTML seguras
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

// 2. INTERNACIONALIZACIÓN: TRADUCCIÓN UNIFICADA (Español)
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
    
    // Indicador visual de procesamiento (Spinner Institucional)
    processing:       '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Procesando...</span></div>',
    
    search:           'Buscar:',
    zeroRecords:      'No se encontraron coincidencias.',
    
    // Configuración de controles de navegación
    paginate: {
        first:    '«',
        last:     '»',
        next:     '›',
        previous: '‹',
    },
    
    // Accesibilidad para lectores de pantalla
    aria: {
        sortAscending:  ': Activar para ordenar la columna descendente',
        sortDescending: ': Activar para ordenar la columna ascendente'
    }
};
