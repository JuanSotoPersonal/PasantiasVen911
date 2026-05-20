/**
 * fichas_comun.js - Utilidades compartidas para el manejo de Fichas (Ven911)
 * 
 * Centraliza los diccionarios visuales y la lógica de visualización detallada
 * para permitir la reutilización de la "Ficha de Vida" en múltiples módulos
 * (Fichas, Reportes, Dashboard) siguiendo el principio de Inercia Cero.
 */

window.FichaUI = {
    // 1. DICCIONARIOS VISUALES (Consistencia de Interfaz)
    badgeClases: {
        'Pendiente':  'badge-pendiente',
        'En Proceso': 'badge-en-proceso',
        'Atendido':   'badge-atendido',
        'Cerrado':    'badge-cerrado',
    },
    iconosEstado: {
        'Pendiente':  'bi-hourglass-split',
        'En Proceso': 'bi-arrow-repeat',
        'Atendido':   'bi-check-circle-fill',
        'Cerrado':    'bi-lock-fill',
    }
};

/**
 * Manejador Global de Visualización de Detalles
 * Captura clics en elementos con clase .btn-ver-detalle para orquestar
 * la carga asíncrona de datos y apertura del modal institucional.
 */
$(document).on('click', '.btn-ver-detalle', function () {
    const fichaId = $(this).data('id');
    
    // Validación de integridad del DOM
    const modalEl = document.getElementById('modalDetalleFicha');
    if (!modalEl) {
        console.error('[Ven911] Error: El modal #modalDetalleFicha no está presente en esta vista.');
        return;
    }

    // Inicialización de UI: Label y Spinner de Carga
    $('#detalleFichaIdLabel').text(`#${fichaId}`);
    $('#contenidoDetalleFicha').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-secondary fw-semibold">Sincronizando información de la emergencia...</p>
        </div>
    `);

    // Apertura del modal mediante Bootstrap 5 API
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    // Consulta asíncrona al Controlador de Fichas
    $.get(`index.php?url=ficha/detalle&id=${fichaId}`, function (res) {
        if (!res.success || !res.data) {
            $('#contenidoDetalleFicha').html(`
                <div class="alert alert-danger border-0 rounded-4 p-4 text-center">
                    <i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>
                    No se pudo recuperar la información de la ficha #${fichaId}.
                </div>
            `);
            return;
        }

        const f = res.data;
        const badgeCls = window.FichaUI.badgeClases[f.estado_ficha] || 'badge-cerrado';
        const icono    = window.FichaUI.iconosEstado[f.estado_ficha] || 'bi-question-circle';

        // Renderizado del cuerpo del detalle (Grid System & Sanetización XSS)
        $('#contenidoDetalleFicha').html(`
            <div class="mb-3">
                <span class="badge-ficha-estado ${badgeCls} fs-6 mb-3 d-inline-block">
                    <i class="bi ${icono}"></i> ${escapeHTML(f.estado_ficha)}
                </span>
            </div>
            <div class="ficha-detalle-grid">
                <div class="ficha-detalle-item"><label>Solicitante</label><span>${escapeHTML(f.nombre_solicitante)}</span></div>
                <div class="ficha-detalle-item"><label>Cédula</label><span>${f.cedula_solicitante ? 'V-' + escapeHTML(f.cedula_solicitante) : '<em class="text-muted">S/D</em>'}</span></div>
                <div class="ficha-detalle-item"><label>Teléfono 1</label><span>${escapeHTML(f.telefono1)}</span></div>
                <div class="ficha-detalle-item"><label>Teléfono 2</label><span>${f.telefono2 ? escapeHTML(f.telefono2) : '<em class="text-muted">N/A</em>'}</span></div>
                <div class="ficha-detalle-item"><label>Municipio</label><span>${escapeHTML(f.nombre_municipio)}</span></div>
                <div class="ficha-detalle-item"><label>Parroquia</label><span>${escapeHTML(f.nombre_parroquia)}</span></div>
                <div class="ficha-detalle-item"><label>Comuna</label><span>${f.nombre_comuna ? escapeHTML(f.nombre_comuna) : '<em class="text-muted">N/A</em>'}</span></div>
                <div class="ficha-detalle-item"><label>Sector</label><span>${f.nombre_sector ? escapeHTML(f.nombre_sector) : '<em class="text-muted">N/A</em>'}</span></div>
                <div class="ficha-detalle-item" style="grid-column:1/-1"><label>Dirección</label><span>${escapeHTML(f.direccion_exacta)}</span></div>
                <div class="ficha-detalle-item"><label>Tipo de Emergencia</label><span>${escapeHTML(f.tipo_emergencia)}</span></div>
                <div class="ficha-detalle-item"><label>Caso</label><span>${escapeHTML(f.nombre_caso)}</span></div>
                <div class="ficha-detalle-item" style="grid-column:1/-1"><label>Descripción</label><span>${escapeHTML(f.descripcion_caso)}</span></div>
                <div class="ficha-detalle-item"><label>Fecha Creación</label><span>${escapeHTML(f.fecha_creacion)}</span></div>
                <div class="ficha-detalle-item"><label>Creado por</label><span>${escapeHTML(f.nombre_creador || 'Sistema')}</span></div>
                
                ${f.motivo_cierre || f.tipo_motivo_cierre ? `
                <div class="ficha-detalle-item mt-2" style="grid-column:1/-1; background-color: rgba(220, 38, 38, 0.05); border-left: 4px solid #dc2626; border-radius: 4px;">
                    <label class="text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Motivo del Cierre</label>
                    <span class="text-dark fw-bold">
                        ${f.tipo_motivo_cierre ? `<span class="badge bg-danger me-2">${escapeHTML(f.tipo_motivo_cierre)}</span>` : ''}
                        ${escapeHTML(f.motivo_cierre || '')}
                    </span>
                </div>` : ''}
            </div>

            ${res.despachos && res.despachos.length > 0 ? `
            <div class="detalle-organismos-section mt-4 pt-3 border-top">
                <h6 class="fw-bold text-success mb-3">
                    <i class="bi bi-shield-shaded me-2"></i>Organismos Asignados
                </h6>
                <div class="organismos-lista">
                    ${res.despachos.map(d => {
                        const estatusCls = {
                            'Asignado':  'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
                            'En Camino': 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            'En Sitio':  'badge bg-info-subtle text-info-emphasis border border-info-subtle',
                            'Liberado':  'badge bg-success-subtle text-success border border-success-subtle',
                            'Cancelado': 'badge bg-danger-subtle text-danger border border-danger-subtle'
                        }[d.estatus_despacho] || 'badge bg-light text-dark border';

                        return `
                        <div class="organismo-item p-3 mb-2 rounded-3 border bg-light-subtle shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small"><i class="bi bi-building me-2"></i>${escapeHTML(d.nombre_organismo)}</span>
                                <span class="${estatusCls} py-1 px-2" style="font-size:0.65rem;">${escapeHTML(d.estatus_despacho)}</span>
                            </div>
                            <div class="row g-2 small text-muted" style="font-size: 0.85rem;">
                                <div class="col-sm-6">
                                    <i class="bi bi-truck me-1"></i><strong>Unidad:</strong> ${escapeHTML(d.unidad_designada)}
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <i class="bi bi-person-badge me-1"></i><strong>Mando:</strong> ${escapeHTML(d.mando_acargo)}
                                </div>
                                <div class="col-12 mt-1">
                                    <i class="bi bi-clock me-1"></i><strong>Despacho:</strong> ${escapeHTML(d.hora_despacho)}
                                    ${d.nombre_despachador ? ` | <i class="bi bi-person-check me-1"></i>${escapeHTML(d.nombre_despachador)}` : ''}
                                </div>
                                ${d.motivo_cancelacion ? `
                                <div class="col-12 mt-2 pt-2 border-top text-danger fw-semibold">
                                    <i class="bi bi-x-circle-fill me-1"></i>Cancelación: ${escapeHTML(d.motivo_cancelacion)}
                                </div>` : ''}
                            </div>
                        </div>`;
                    }).join('')}
                </div>
            </div>` : (res.despachos ? `
            <div class="detalle-organismos-section mt-4 text-center py-3 bg-light rounded-4 border border-dashed">
                <p class="text-muted mb-0 small"><i class="bi bi-info-circle me-1"></i>No hay organismos asignados a esta ficha aún.</p>
            </div>` : '')}
        `);
    }, 'json').fail(() => {
        $('#contenidoDetalleFicha').html(`
            <div class="alert alert-danger border-0 rounded-4 p-4 text-center">
                <i class="bi bi-wifi-off display-4 d-block mb-2"></i>
                Error de conexión con el servidor.
            </div>
        `);
    });
});
