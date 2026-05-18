/**
 * despacho_datatable.js - Centro de Despacho VEN 911
 *
 * FLUJO OPERATIVO:
 * 1. Tabla principal muestra TODAS las fichas Pendiente + En Proceso (vista global)
 * 2. Ficha "Pendiente": botón "Tomar Ficha" → id_owner = usuario actual, estado → En Proceso
 * 3. Ficha "En Proceso": botón "Gestionar" → modal con resumen de ficha + lista de despachos
 * 4. Desde el modal de gestión: botón "Asignar Organismo" (ficha_id pre-inyectado)
 * 5. Cada despacho en la lista tiene botones de avance de estatus
 */

$(document).ready(function () {

    // ///////////////////////////////////////////////////////////////////
    // 1. CONFIGURACIÓN INICIAL (PERMISOS RBAC)
    // ///////////////////////////////////////////////////////////////////

    const puedeCrear      = window.VEN911_PERM_DESPACHO_CREAR  ?? false;
    const puedeEditar     = window.VEN911_PERM_DESPACHO_EDITAR ?? false;
    const puedeCambiarEst = window.VEN911_PERM_DESPACHO_ESTADO ?? false;

    // Diccionarios visuales para estados de fichas
    const badgeFichaClases = {
        'Pendiente':  'badge-pendiente',
        'En Proceso': 'badge-en-proceso',
    };
    const iconosFicha = {
        'Pendiente':  'bi-hourglass-split',
        'En Proceso': 'bi-arrow-repeat',
    };

    // Diccionarios visuales para estatus de despachos
    const badgeDespachoClases = {
        'Asignado':  'badge-despacho-asignado',
        'En Camino': 'badge-despacho-en-camino',
        'En Sitio':  'badge-despacho-en-sitio',
        'Liberado':  'badge-despacho-liberado',
        'Cancelado': 'badge-despacho-cancelado',
    };
    const iconosDespacho = {
        'Asignado':  'bi-broadcast',
        'En Camino': 'bi-car-front-fill',
        'En Sitio':  'bi-geo-alt-fill',
        'Liberado':  'bi-check-circle-fill',
        'Cancelado': 'bi-x-circle-fill',
    };

    // Flujo de transiciones válidas por estatus actual del despacho
    const transicionesDespacho = {
        'Asignado':  [['En Camino', 'btn-warning',    'bi-car-front-fill']],
        'En Camino': [['En Sitio',  'btn-success',    'bi-geo-alt-fill']],
        'En Sitio':  [['Liberado',  'btn-secondary',  'bi-check-circle-fill']],
        // 'Liberado' y 'Cancelado' son terminales: sin transiciones
    };

    // ///////////////////////////////////////////////////////////////////
    // 2. DATATABLE PRINCIPAL (FICHAS ACTIVAS — GLOBAL)
    // ///////////////////////////////////////////////////////////////////

    let tablaDespachos = null;
    let tablaDespachosPropias = null;

    if ($('#tablaDespachos').length) {
        tablaDespachos = $('#tablaDespachos').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url:  'index.php?url=despacho/obtenerDatos',
            type: 'POST',
            error: function () {
                Swal.fire('Error de Conexión', 'No se pudo cargar la tabla de fichas.', 'error');
            },
        },
        columns: [
            {
                // Columna: Contador de registros
                data: null,
                width: '50px',
                orderable: false,
                searchable: false,
                render: (d, type, row, meta) =>
                    `<span class="fw-bold text-secondary">${meta.row + meta.settings._iDisplayStart + 1}</span>`
            },
            {
                // Columna: Ficha + Solicitante
                data: 'nombre_solicitante',
                render: (d, type, row) => `
                    <div class="fw-bold text-success">#${escapeHTML(String(row.id))}</div>
                    <div class="fw-semibold">${escapeHTML(d)}</div>
                    <small class="text-muted"><i class="bi bi-telephone-fill me-1"></i>${escapeHTML(row.telefono1)}</small>`
            },
            {
                // Columna: Tipo / Caso
                data: 'nombre_caso',
                render: (d, type, row) => `
                    <div class="fw-semibold">${escapeHTML(d)}</div>
                    <small class="text-muted">${escapeHTML(row.tipo_emergencia)}</small>`
            },
            {
                // Columna: Ubicación geográfica
                data: 'nombre_parroquia',
                render: (d, type, row) => `
                    <div>${escapeHTML(d)}</div>
                    <small class="text-muted">${escapeHTML(row.nombre_municipio)}</small>`
            },
            {
                // Columna: Estado de la ficha (badge dinámico)
                data: 'estado_ficha',
                render: (d) => {
                    const cls  = badgeFichaClases[d]  || 'badge-cerrado';

                    const icon = iconosFicha[d]        || 'bi-question-circle';
                    return `<span class="badge-ficha-estado ${cls}"><i class="bi ${icon}"></i>${escapeHTML(d)}</span>`;
                }
            },
            {
                // Columna: Responsable actual (id_owner)
                data: 'nombre_owner',
                render: (d) => d
                    ? `<span class="fw-semibold"><i class="bi bi-person-fill me-1 text-success"></i>${escapeHTML(d)}</span>`
                    : `<span class="text-muted fst-italic">Sin asignar</span>`
            },
            {
                // Columna: Cantidad de organismos despachados
                data: 'total_despachos',
                orderable: false,
                className: 'text-center',
                render: (d) => parseInt(d) > 0
                    ? `<span class="badge bg-success rounded-pill">${d}</span>`
                    : `<span class="badge bg-secondary rounded-pill">0</span>`
            },
            {
                // Columna: Fecha de apertura de la ficha
                data: 'fecha_creacion',
                render: (d) => `<small class="text-muted">${d}</small>`
            },
            {
                // Columna: Acciones dinámicas según el estado de la ficha
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: (d, type, row) => {
                    if (row.estado_ficha === 'Pendiente' && puedeCrear) {
                        // Ficha Pendiente: botón para que el despachador la tome
                        return `<button class="btn btn-ven-primary btn-sm btn-tomar-ficha px-2"
                                        data-id="${row.id}"
                                        title="Tomar esta ficha"
                                        id="btnTomar-${row.id}">
                                    <i class="bi bi-hand-index-fill me-1"></i>Tomar
                                </button>`;
                    }
                    if (row.estado_ficha === 'En Proceso') {
                        // Ficha En Proceso: botón para gestionar sus despachos
                        return `<button class="btn btn-ven-primary btn-sm btn-gestionar-despachos px-2"
                                        data-id="${row.id}"
                                        title="Gestionar despachos de esta ficha"
                                        id="btnGestionar-${row.id}">
                                    <i class="bi bi-broadcast me-1"></i>Gestionar
                                </button>`;
                    }
                    // Estados terminales: solo lectura
                    return `<span class="text-muted fst-italic small">—</span>`;
                }
            }
        ],
        language:   window.Ven911DataTablesLang,
        order:      [[7, 'asc']], // Más antiguas primero
        responsive: true,
        pageLength: 15,
        searchDelay: 600, // Debounce para optimizar búsquedas
        drawCallback: function (settings) {
            // Actualiza el contador del badge del tab "Cola General"
            const total = settings.json ? settings.json.recordsFiltered : 0;
            $('#contadorGeneral').text(total);
        },
    });
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. DATATABLE PROPIA (FICHAS DEL DESPACHADOR ACTUAL)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Tabla filtrada por id_owner: solo muestra las fichas que el
     * despachador en sesión tomó durante su turno.
     * Mismas columnas y acciones que la tabla global.
     */
    if ($('#tablaDespachosPropias').length) {
        tablaDespachosPropias = $('#tablaDespachosPropias').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url:  'index.php?url=despacho/obtenerDatosPropios',
            type: 'POST',
            error: function () {
                Swal.fire('Error de Conexión', 'No se pudieron cargar tus fichas.', 'error');
            },
        },
        columns: [
            {
                data: null, width: '50px', orderable: false, searchable: false,
                render: (d, type, row, meta) =>
                    `<span class="fw-bold text-secondary">${meta.row + meta.settings._iDisplayStart + 1}</span>`
            },
            {
                data: 'nombre_solicitante',
                render: (d, type, row) => `
                    <div class="fw-bold text-primary">#${escapeHTML(String(row.id))}</div>
                    <div class="fw-semibold">${escapeHTML(d)}</div>
                    <small class="text-muted"><i class="bi bi-telephone-fill me-1"></i>${escapeHTML(row.telefono1)}</small>`
            },
            {
                data: 'nombre_caso',
                render: (d, type, row) => `
                    <div class="fw-semibold">${escapeHTML(d)}</div>
                    <small class="text-muted">${escapeHTML(row.tipo_emergencia)}</small>`
            },
            {
                data: 'nombre_parroquia',
                render: (d, type, row) => `
                    <div>${escapeHTML(d)}</div>
                    <small class="text-muted">${escapeHTML(row.nombre_municipio)}</small>`
            },
            {
                data: 'estado_ficha',
                render: (d) => {
                    const cls  = badgeFichaClases[d] || 'badge-cerrado';
                    const icon = iconosFicha[d]       || 'bi-question-circle';
                    return `<span class="badge-ficha-estado ${cls}"><i class="bi ${icon}"></i>${escapeHTML(d)}</span>`;
                }
            },
            {
                data: 'nombre_owner',
                render: (d) => d
                    ? `<span class="fw-semibold"><i class="bi bi-person-fill me-1 text-primary"></i>${escapeHTML(d)}</span>`
                    : `<span class="text-muted fst-italic">Sin asignar</span>`
            },
            {
                data: 'total_despachos', orderable: false, className: 'text-center',
                render: (d) => parseInt(d) > 0
                    ? `<span class="badge bg-success rounded-pill">${d}</span>`
                    : `<span class="badge bg-secondary rounded-pill">0</span>`
            },
            {
                data: 'fecha_creacion',
                render: (d) => `<small class="text-muted">${d}</small>`
            },
            {
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: (d, type, row) => {
                    if (row.estado_ficha === 'Pendiente' && puedeCrear) {
                        return `<button class="btn btn-ven-primary btn-sm btn-tomar-ficha px-2"
                                        data-id="${row.id}" title="Tomar esta ficha" id="btnTomarP-${row.id}">
                                    <i class="bi bi-hand-index-fill me-1"></i>Tomar
                                </button>`;
                    }
                    if (row.estado_ficha === 'En Proceso') {
                        return `<button class="btn btn-ven-primary btn-sm btn-gestionar-despachos px-2"
                                        data-id="${row.id}" title="Gestionar despachos" id="btnGestionarP-${row.id}">
                                    <i class="bi bi-broadcast me-1"></i>Gestionar
                                </button>`;
                    }
                    return `<span class="text-muted fst-italic small">—</span>`;
                }
            }
        ],
        language:   window.Ven911DataTablesLang,
        order:      [[7, 'asc']],
        responsive: true,
        pageLength: 15,
        searchDelay: 600, // Debounce para optimizar búsquedas
        drawCallback: function (settings) {
            // Actualiza el contador del badge del tab "Mis Fichas"
            const total = settings.json ? settings.json.recordsFiltered : 0;
            $('#contadorPropias').text(total);
        },
    });
    }

    // Cuando se cambia al tab "Mis Fichas", ajustar columnas (layout fix)
    if (document.getElementById('tab-propias')) {
        document.getElementById('tab-propias').addEventListener('shown.bs.tab', function () {
            if (tablaDespachosPropias) tablaDespachosPropias.columns.adjust().draw(false);
        });
    }

    /**
     * Recarga las tablas existentes en el DOM
     */
    function recargarTablas() {
        if (tablaDespachos) tablaDespachos.ajax.reload(null, false);
        if (tablaDespachosPropias) tablaDespachosPropias.ajax.reload(null, false);
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. ACCIÓN: TOMAR FICHA (CAMBIO DE OWNERSHIP)
    // ///////////////////////////////////////////////////////////////////


    $(document).on('click', '.btn-tomar-ficha', function () {
        const fichaId    = $(this).data('id');
        const $btn       = $(this);

        Swal.fire({
            title: `¿Tomar la Ficha #${fichaId}?`,
            html:  'Serás registrado como responsable de esta ficha.<br>' +
                   '<small class="text-muted">El estado cambiará a <strong>En Proceso</strong>.</small>',
            icon:  'question',
            showCancelButton:  true,
            confirmButtonText: '<i class="bi bi-hand-index-fill me-1"></i>Sí, tomar ficha',
            cancelButtonText:  'Cancelar',
        }).then(result => {
            if (!result.isConfirmed) return;

            $btn.prop('disabled', true).html('<div class="spinner-border spinner-border-sm"></div>');

            $.post('index.php?url=despacho/tomarFicha', { ficha_id: fichaId }, function (res) {
                if (res.success) {
                    recargarTablas();
                    Swal.fire({
                        title: '¡Ficha Tomada!',
                        text:  res.message,
                        icon:  'success',
                        timer: 2000,
                        showConfirmButton: false,
                    }).then(() => {
                        // Abrir automáticamente el modal de gestión para que el despachador pueda asignar organismos de inmediato
                        abrirModalGestion(fichaId);
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                    recargarTablas();

                }
            }, 'json').fail(() => {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                $btn.prop('disabled', false).html('<i class="bi bi-hand-index-fill me-1"></i>Tomar');
            });
        });
    });

    // ///////////////////////////////////////////////////////////////////
    // 4. MODAL DE GESTIÓN: FICHA + SUS DESPACHOS
    // ///////////////////////////////////////////////////////////////////

    $(document).on('click', '.btn-gestionar-despachos', function () {
        const fichaId = $(this).data('id');
        abrirModalGestion(fichaId);
    });

    /**
     * Carga y muestra el modal de gestión de una ficha específica.
     * Incluye resumen de ficha y lista de despachos con botones de avance.
     */
    function abrirModalGestion(fichaId) {
        $('#detalleDespachoIdLabel').text(`#${fichaId}`);
        $('#contenidoResumenFicha').html('<div class="text-center py-4"><div class="spinner-border text-success"></div></div>');
        $('#contenidoListaDespachos').html('<div class="text-center py-3"><div class="spinner-border text-success"></div></div>');
        $('#btnAgregarOrganismoDesdeDetalle').addClass('d-none').data('ficha-id', fichaId).attr('data-ficha-id', fichaId);
        $('#btnEditarFichaDesdeDetalle').addClass('d-none').data('ficha-id', fichaId).attr('data-ficha-id', fichaId);
        $('#seccionCambioEstadoFicha').addClass('d-none');
        $('#contenedorStepperFicha').empty();
        $('#contenedorBotonesEstadoFicha').empty();


        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleDespacho')).show();

        $.get(`index.php?url=despacho/detalleFicha&id=${fichaId}`, function (res) {
            if (!res.success || !res.ficha) {
                $('#contenidoResumenFicha').html('<p class="text-danger text-center"><i class="bi bi-exclamation-triangle me-1"></i>No se pudo cargar la ficha.</p>');
                return;
            }

            const f   = res.ficha;
            const cls = badgeFichaClases[f.estado_ficha] || 'badge-cerrado';

            const ico = iconosFicha[f.estado_ficha]       || 'bi-question-circle';

            // 4.1 Renderizado del resumen de la ficha
            $('#contenidoResumenFicha').html(`
                <div class="mb-3 d-flex align-items-center gap-3 flex-wrap">
                    <span class="badge-ficha-estado ${cls} fs-6">
                        <i class="bi ${ico}"></i> ${escapeHTML(f.estado_ficha)}
                    </span>
                    ${f.nombre_owner
                        ? `<span class="text-muted small"><i class="bi bi-person-fill me-1 text-success"></i>Responsable: <strong>${escapeHTML(f.nombre_owner)}</strong></span>`
                        : `<span class="text-muted small fst-italic">Sin responsable asignado</span>`}
                </div>

                <div class="despacho-detalle-grid">
                    <div class="despacho-detalle-item"><label>Solicitante</label><span>${escapeHTML(f.nombre_solicitante)}</span></div>
                    <div class="despacho-detalle-item"><label>Teléfono</label><span>${escapeHTML(f.telefono1)}${f.telefono2 ? ' / ' + escapeHTML(f.telefono2) : ''}</span></div>
                    <div class="despacho-detalle-item"><label>Tipo de Emergencia</label><span>${escapeHTML(f.tipo_emergencia)}</span></div>
                    <div class="despacho-detalle-item"><label>Caso</label><span>${escapeHTML(f.nombre_caso)}</span></div>
                    <div class="despacho-detalle-item"><label>Parroquia</label><span>${escapeHTML(f.nombre_parroquia)}</span></div>
                    <div class="despacho-detalle-item"><label>Municipio</label><span>${escapeHTML(f.nombre_municipio)}</span></div>
                    <div class="despacho-detalle-item" style="grid-column:1/-1"><label>Dirección</label><span>${escapeHTML(f.direccion_exacta)}</span></div>
                    <div class="despacho-detalle-item" style="grid-column:1/-1"><label>Descripción</label><span>${escapeHTML(f.descripcion_caso)}</span></div>
                </div>
            `);

            // 4.2 Stepper de estado de la ficha + botones de control manual
            renderizarStepperFicha(f.estado_ficha, fichaId);

            // 4.3 Botón de edición de ficha (solo estados no terminales)
            const estadosTerminales = ['Cerrado'];


            if (window.VEN911_PERM_FICHA_EDITAR && !estadosTerminales.includes(f.estado_ficha)) {
                $('#btnEditarFichaDesdeDetalle').removeClass('d-none');
            }

            // 4.4 Lista de despachos asignados
            renderizarListaDespachos(res.despachos);

            // 4.5 Botón "Asignar Organismo" solo para fichas En Proceso
            if (f.estado_ficha === 'En Proceso' && puedeCrear) {
                $('#btnAgregarOrganismoDesdeDetalle')
                    .removeClass('d-none')
                    .data('ficha-id', fichaId)
                    .attr('data-ficha-id', fichaId)
                    .data('sector-id', f.sector_id || 0)
                    .attr('data-sector-id', f.sector_id || 0);
            }

        }, 'json');
    }

    /**
     * Renderiza la lista de organismos despachados para una ficha.
     * Genera cards informativas con badges de estatus y botones de avance.
     */
    function renderizarListaDespachos(despachos) {
        if (!despachos || despachos.length === 0) {
            $('#contenidoListaDespachos').html(`
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    <p class="mb-0">No hay organismos despachados aún.</p>
                    <small>Usa el botón "Asignar Organismo" para enviar unidades a esta emergencia.</small>
                </div>`);
            return;
        }

        let html = '<div class="d-flex flex-column gap-3">';
        despachos.forEach(d => {
            const cls  = badgeDespachoClases[d.estatus_despacho] || 'badge-despacho-asignado';
            const ico  = iconosDespacho[d.estatus_despacho]       || 'bi-broadcast';
            const trns = transicionesDespacho[d.estatus_despacho] || [];

            // Mini-stepper de progresión del organismo
            const miniStepper = renderizarMiniStepperDespacho(d.estatus_despacho);

            let btnsAvance = '';
            const esTerminalDespacho = ['Liberado', 'Cancelado'].includes(d.estatus_despacho);

            if (puedeCambiarEst && trns.length > 0) {
                trns.forEach(([nuevoEstado, btnCls, btnIco]) => {
                    btnsAvance += `
                        <button class="btn ${btnCls} btn-sm btn-avanzar-despacho ms-1"
                                data-id="${d.id}" data-estado="${nuevoEstado}"
                                style="font-size:0.75rem; padding: 0.25rem 0.6rem;">
                            <i class="bi ${btnIco} me-1"></i>${nuevoEstado}
                        </button>`;
                });
            }

            // Botón "Cancelar Llamada" disponible para estatus no terminales
            const btnCancelar = (!esTerminalDespacho && puedeCambiarEst)
                ? `<button class="btn btn-outline-danger btn-sm btn-cancelar-despacho ms-1"
                           data-id="${d.id}" data-organismo="${escapeHTML(d.nombre_organismo)}"
                           style="font-size:0.75rem; padding: 0.25rem 0.6rem;" title="Cancelar llamada al organismo">
                       <i class="bi bi-x-circle me-1"></i>Cancelar
                   </button>`
                : '';

            // Si fue cancelado, mostrar el motivo de cancelación
            const infoCancelacion = (d.estatus_despacho === 'Cancelado' && d.motivo_cancelacion)
                ? `<div class="text-danger small mt-1"><i class="bi bi-x-circle-fill me-1"></i><strong>Motivo:</strong> ${escapeHTML(d.motivo_cancelacion)}</div>`
                : '';

            html += `
                <div class="despacho-card p-3 rounded-3 border" style="background: ${d.estatus_despacho === 'Cancelado' ? '#fff5f5' : '#f9fafb'};">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-bold fs-6 d-flex align-items-center gap-2 flex-wrap">
                                <span>${escapeHTML(d.nombre_organismo)}</span>
                                ${d.nombre_cuadrante ? `<span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold rounded-pill" style="font-size:0.7rem; padding: 0.15rem 0.4rem;"><i class="bi bi-geo-alt-fill me-1"></i>${escapeHTML(d.nombre_cuadrante)}</span>` : ''}
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-shield-fill me-1"></i><strong>Unidad:</strong> ${escapeHTML(d.unidad_designada || 'N/A')}
                                &nbsp;·&nbsp;
                                <i class="bi bi-person-badge-fill me-1"></i><strong>Mando:</strong> ${escapeHTML(d.mando_acargo || 'N/A')}
                                ${d.persona_atiende ? `&nbsp;·&nbsp;<i class="bi bi-person-fill me-1"></i><strong>Atiende:</strong> ${escapeHTML(d.persona_atiende)}` : ''}
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-clock me-1"></i>${escapeHTML(d.hora_despacho)}
                                ${d.nombre_despachador ? `&nbsp;·&nbsp;<i class="bi bi-person-check-fill me-1 text-success"></i>${escapeHTML(d.nombre_despachador)}` : ''}
                            </div>
                            ${infoCancelacion}
                            ${miniStepper}
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            <span class="badge-despacho-estatus ${cls}">
                                <i class="bi ${ico}"></i>${escapeHTML(d.estatus_despacho)}
                            </span>
                            ${btnsAvance}
                            ${btnCancelar}
                        </div>
                    </div>
                </div>`;
        });
        html += '</div>';
        $('#contenidoListaDespachos').html(html);
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. AVANCE DE ESTATUS DE DESPACHO (DESDE EL MODAL DE GESTIÓN)
    // ///////////////////////////////////////////////////////////////////

    $(document).on('click', '.btn-avanzar-despacho', function () {
        const despachoId  = $(this).data('id');
        const nuevoEstado = $(this).data('estado');
        const fichaId     = parseInt($('#detalleDespachoIdLabel').text().replace('#', ''));

        Swal.fire({
            title: `¿Avanzar a "${nuevoEstado}"?`,
            text:  'Este cambio quedará registrado en el historial de trazabilidad.',
            icon:  'question',
            showCancelButton:  true,
            confirmButtonText: 'Sí, avanzar',
            cancelButtonText:  'Cancelar',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post(
                'index.php?url=despacho/cambiarEstado',
                { despacho_id: despachoId, nuevo_estado: nuevoEstado },
                function (res) {
                    if (res.success) {
                        Swal.fire({ title: '¡Actualizado!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
                        // Recargar solo la lista de despachos dentro del modal
                        recargarDespachosDeFicha(fichaId);
                        // Refrescar la tabla principal silenciosamente
                        tablaDespachos.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                'json'
            );
        });
    });

    /**
     * Recarga únicamente la lista de despachos del modal (sin cerrarlo).
     */
    function recargarDespachosDeFicha(fichaId) {
        $('#contenidoListaDespachos').html('<div class="text-center py-3"><div class="spinner-border text-success"></div></div>');
        $.get(`index.php?url=despacho/detalleFicha&id=${fichaId}`, function (res) {
            if (res.success) {
                renderizarListaDespachos(res.despachos);
            }
        }, 'json');
    }

    // ///////////////////////////////////////////////////////////////////
    // 5b. CANCELACIÓN DE DESPACHO DE ORGANISMO
    //     Flujo: selección de motivo del catálogo → descripción libre → POST
    // ///////////////////////////////////////////////////////////////////

    $(document).on('click', '.btn-cancelar-despacho', function () {
        const despachoId    = $(this).data('id');
        const nombreOrg     = $(this).data('organismo');
        const fichaId       = parseInt($('#detalleDespachoIdLabel').text().replace('#', ''));

        // Paso 1: Cargar motivos del catálogo y pedir confirmación
        $.get('index.php?url=ficha/obtenerCatalogo&cat=motivo_cierre&estado=1&contexto=organismo', function (res) {

            let opcionesMotivo = '<option value="">-- Seleccione un motivo --</option>';
            if (res && res.data && res.data.length > 0) {
                res.data.forEach(m => {
                    opcionesMotivo += `<option value="${escapeHTML(m.nombre)}">${escapeHTML(m.nombre)}</option>`;
                });
            } else {
                // Fallback si el catálogo está vacío
                opcionesMotivo += '<option value="Sin respuesta">Sin respuesta</option>';
                opcionesMotivo += '<option value="Unidad no disponible">Unidad no disponible</option>';
                opcionesMotivo += '<option value="Error de asignación">Error de asignación</option>';
            }

            Swal.fire({
                title: `Cancelar: ${nombreOrg}`,
                icon: 'warning',
                html: `
                    <p class="text-muted small mb-3">Esta acción cancelará el despacho del organismo y quedará registrada en el historial de trazabilidad.</p>
                    <div class="text-start mb-3">
                        <label class="form-label fw-semibold small">Motivo de Cancelación <span class="text-danger">*</span></label>
                        <select id="swal-tipo-motivo" class="form-select form-select-sm">
                            ${opcionesMotivo}
                        </select>
                    </div>
                    <div class="text-start">
                        <label class="form-label fw-semibold small">Descripción Adicional <span class="text-muted">(opcional)</span></label>
                        <textarea id="swal-descripcion" class="form-control form-control-sm" rows="2"
                                  placeholder="Detalles adicionales sobre la cancelación..."></textarea>
                    </div>`,
                showCancelButton:  true,
                confirmButtonText: '<i class="bi bi-x-circle-fill me-1"></i>Cancelar Despacho',
                cancelButtonText:  'Volver',
                confirmButtonColor: '#dc3545',
                didOpen: () => {
                    // SweetAlert2 retiene el foco en el botón de confirmación por defecto,
                    // bloqueando la escritura en el textarea. Liberamos el foco manualmente.
                    document.getElementById('swal-tipo-motivo').focus();
                },
                preConfirm: () => {
                    const tipo = document.getElementById('swal-tipo-motivo').value.trim();
                    const desc = document.getElementById('swal-descripcion').value.trim();
                    if (!tipo) {
                        Swal.showValidationMessage('Debe seleccionar un motivo de cancelación.');
                        return false;
                    }
                    return { tipo_motivo: tipo, descripcion: desc };
                },
            }).then(result => {
                if (!result.isConfirmed) return;

                $.post(
                    'index.php?url=despacho/cancelarDespacho',
                    {
                        despacho_id: despachoId,
                        tipo_motivo: result.value.tipo_motivo,
                        descripcion: result.value.descripcion,
                    },
                    function (res) {
                        if (res.success) {
                            Swal.fire({ title: 'Cancelado', text: res.message, icon: 'success', timer: 1800, showConfirmButton: false });
                            recargarDespachosDeFicha(fichaId);
                            tablaDespachos.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    'json'
                ).fail(() => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'));
            });

        }, 'json').fail(() => Swal.fire('Error', 'No se pudo cargar el catálogo de motivos.', 'error'));
    });

    // ///////////////////////////////////////////////////////////////////
    // 6. CONTROL DE ESTADO DE FICHA (STEPPER + CAMBIO MANUAL)
    // ///////////////////////////////////////////////////////////////////

    // Orden canónico de los estados de la ficha (ruta principal)
    // Flujo: Pendiente → En Proceso → Atendido → Cerrado
    const PASOS_FICHA = ['Pendiente', 'En Proceso', 'Atendido', 'Cerrado'];

    // Ícono por estado de ficha
    const ICONOS_PASOS = {
        'Pendiente':  'bi-clock',
        'En Proceso': 'bi-arrow-repeat',
        'Atendido':   'bi-check-circle-fill',
        'Cerrado':    'bi-lock-fill',
    };

    // Transiciones válidas por estado
    // Cerrado es el único estado terminal en este modelo

    const TRANSICIONES_FICHA = {
        'Pendiente':  [
            { estado: 'En Proceso', cls: 'btn-ven-primary', ico: 'bi-arrow-repeat' },
        ],
        'En Proceso': [
            { estado: 'Atendido', cls: 'btn-ven-primary', ico: 'bi-check-circle-fill' },
            { estado: 'Cerrado',  cls: 'btn-secondary',   ico: 'bi-lock-fill' },
        ],
        'Atendido': [],
    };

    /**
     * Renderiza el stepper visual de progresión de estados de la ficha.
     * Flujo único: Pendiente → En Proceso → Atendido → Cerrado
     */
    function renderizarStepperFicha(estadoActual, fichaId) {
        if (!window.VEN911_PERM_FICHA_ESTADO && !window.VEN911_PERM_DESPACHO_ESTADO) {
            $('#seccionCambioEstadoFicha').addClass('d-none');
            return;
        }

        const esTerminal = estadoActual === 'Cerrado' || estadoActual === 'Atendido';

        // Construir el stepper visual (flujo lineal: todos los pasos son secuenciales)
        const indiceActual = PASOS_FICHA.indexOf(estadoActual);

        let htmlStepper = '<div class="ven-stepper">';
        PASOS_FICHA.forEach((paso, idx) => {
            let clsCirculo, clsEtiqueta;

            if (idx < indiceActual) {
                clsCirculo  = 'completado';
                clsEtiqueta = 'completado';
            } else if (idx === indiceActual) {
                clsCirculo  = 'activo';
                clsEtiqueta = 'activo';
            } else {
                clsCirculo  = 'futuro';
                clsEtiqueta = 'futuro';
            }

            const icono = idx < indiceActual
                ? `<i class="bi bi-check-lg"></i>`
                : `<i class="bi ${ICONOS_PASOS[paso]}"></i>`;

            // Línea conectora antes de cada paso (excepto el primero)
            if (idx > 0) {
                const clsLinea = idx <= indiceActual ? 'completada' : 'pendiente';
                htmlStepper += `<div class="ven-step-line ${clsLinea}"></div>`;
            }

            htmlStepper += `
                <div class="ven-step-wrapper">
                    <div class="ven-step-circle ${clsCirculo}">${icono}</div>
                    <div class="ven-step-label ${clsEtiqueta}">${escapeHTML(paso)}</div>
                </div>`;
        });
        htmlStepper += '</div>';

        $('#contenedorStepperFicha').html(htmlStepper);

        // Construir botones de avance
        let htmlBotones = '';
        if (esTerminal) {
            htmlBotones = `
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <i class="bi bi-lock-fill text-secondary"></i>
                    <span>Estado terminal — esta ficha ya no puede avanzar.</span>
                </div>`;
        } else {
            const transiciones = TRANSICIONES_FICHA[estadoActual] || [];

            // Botones de avance rápido
            let htmlBtnsRapidos = transiciones.map(t => `
                <button class="btn ${t.cls} btn-sm btn-cambiar-estado-ficha"
                        data-ficha-id="${fichaId}" data-estado="${t.estado}"
                        style="font-size:0.8rem;">
                    <i class="bi ${t.ico} me-1"></i>${t.estado}
                </button>`).join('');

            htmlBotones = `
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small fw-semibold me-1">
                        <i class="bi bi-arrow-right-circle me-1 text-success"></i>Cambiar a:
                    </span>
                    ${htmlBtnsRapidos}
                </div>`;
        }

        $('#contenedorBotonesEstadoFicha').html(htmlBotones);
        $('#seccionCambioEstadoFicha').removeClass('d-none');
    }

    // Orden canónico de estados de despacho de organismo
    const PASOS_DESPACHO = ['Asignado', 'En Camino', 'En Sitio', 'Liberado'];

    /**
     * Genera el HTML del mini-stepper de un organismo despachado.
     * Muestra los 4 pasos con su posición actual.
     */
    function renderizarMiniStepperDespacho(estatusActual) {
        const idx  = PASOS_DESPACHO.indexOf(estatusActual);
        let   html = '<div class="d-flex align-items-center gap-2 mt-2 flex-wrap">';
        html += '<div class="ven-mini-stepper">';
        PASOS_DESPACHO.forEach((paso, i) => {
            let cls = i < idx ? 'completado' : (i === idx ? 'activo' : 'futuro');
            if (i > 0) {
                html += `<div class="ven-mini-line ${i <= idx ? 'completada' : 'pendiente'}"></div>`;
            }
            html += `<div class="ven-mini-step ${cls}" title="${escapeHTML(paso)}"></div>`;
        });
        html += '</div>';

        // Etiquetas de inicio y fin del stepper
        html += `<span class="text-muted" style="font-size:0.65rem;">${escapeHTML(PASOS_DESPACHO[0])}</span>`;
        html += `<div style="flex:1; height:1px; background:#e5e7eb; min-width:8px;"></div>`;
        html += `<span class="text-muted" style="font-size:0.65rem;">${escapeHTML(PASOS_DESPACHO[PASOS_DESPACHO.length - 1])}</span>`;
        html += '</div>';
        return html;
    }



    $(document).on('click', '.btn-cambiar-estado-ficha', function () {
        const fichaId     = $(this).data('ficha-id');
        const nuevoEstado = $(this).data('estado');

        // Al cerrar una ficha, se exige el motivo (igual que en el módulo de fichas)
        if (nuevoEstado === 'Cerrado') {
            const modalDetalle = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleDespacho'));
            modalDetalle.hide();

            // Cargar motivos estructurados desde el servidor
            $.get('index.php?url=ficha/obtenerCatalogo&cat=motivo_cierre&estado=1&contexto=ficha', function(res) {
                let optionsHtml = '<option value="">-- Seleccione un tipo --</option>';
                if (res && res.data && res.data.length > 0) {
                    res.data.forEach(m => {
                        optionsHtml += `<option value="${escapeHTML(m.nombre)}">${escapeHTML(m.nombre)}</option>`;
                    });
                } else {
                    optionsHtml += `
                        <option value="Llamada Falsa / Sabotaje">Llamada Falsa / Sabotaje</option>
                        <option value="Registro Duplicado">Registro Duplicado</option>
                        <option value="Error de Datos / Prueba">Error de Datos / Prueba</option>
                        <option value="Ficha Atendida / Exitosa">Ficha Atendida / Exitosa</option>
                        <option value="Falta de Recursos / Unidades">Falta de Recursos / Unidades</option>
                        <option value="Otro">Otro Motivo</option>
                    `;
                }

                Swal.fire({
                    title: 'Cerrar Ficha',
                    html: `
                        <p class="text-muted small mb-3">Indique el motivo estructurado del cierre de la ficha.</p>
                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold text-success small mb-1">Tipo de Motivo</label>
                            <select id="swal_tipo_motivo" class="form-select shadow-sm">
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="mb-2 text-start">
                            <label class="form-label fw-bold text-success small mb-1">Descripción del Cierre</label>
                            <textarea id="swal_desc_motivo" class="form-control shadow-sm" rows="3" placeholder="Detalles operativos sobre el cierre..."></textarea>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-lock-fill me-1"></i>Cerrar Ficha',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const tipo = document.getElementById('swal_tipo_motivo').value;
                        const desc = document.getElementById('swal_desc_motivo').value.trim();

                        if (!tipo) {
                            Swal.showValidationMessage('Debe seleccionar un Tipo de Motivo.');
                            return false;
                        }
                        if (desc.length < 5) {
                            Swal.showValidationMessage('La descripción debe tener al menos 5 caracteres.');
                            return false;
                        }

                        return { tipo: tipo, desc: desc };
                    }
                }).then(result => {
                    if (!result.isConfirmed) {
                        modalDetalle.show();
                        return;
                    }
                    enviarCambioEstadoFicha(fichaId, nuevoEstado, result.value.desc, result.value.tipo);
                });
            }, 'json').fail(() => {
                // Fallback si falla el AJAX
                Swal.fire('Error', 'No se pudieron cargar los motivos del sistema.', 'error').then(() => {
                    modalDetalle.show();
                });
            });
            return;
        }

        // Confirmación estándar para estados sin motivo
        Swal.fire({
            title: `¿Cambiar estado a "${nuevoEstado}"?`,
            text:  'El cambio quedará registrado en el historial de la ficha.',
            icon:  'warning',
            showCancelButton:  true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText:  'Cancelar',
        }).then(result => {
            if (!result.isConfirmed) return;
            enviarCambioEstadoFicha(fichaId, nuevoEstado, '');
        });
    });

    /**
     * Envía el cambio de estado de ficha desde el módulo de despacho.
     * Usa el endpoint centralizado del FichaControlador para consistencia.
     */
    function enviarCambioEstadoFicha(fichaId, nuevoEstado, motivoCierre, tipoMotivo = '') {
        $.post(
            'index.php?url=despacho/cambiarEstadoFicha',
            { ficha_id: fichaId, nuevo_estado: nuevoEstado, motivo_cierre: motivoCierre, tipo_motivo: tipoMotivo },
            function (res) {
                if (res.success) {
                    Swal.fire({ title: '¡Estado actualizado!', text: res.message, icon: 'success', timer: 1800, showConfirmButton: false });
                    setTimeout(() => abrirModalGestion(fichaId), 1900);
                    recargarTablas();
                } else {
                    Swal.fire('Error', res.message, 'error').then(() => {
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleDespacho')).show();
                    });
                }
            },
            'json'
        ).fail(() => {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error').then(() => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleDespacho')).show();
            });
        });
    }



    // ///////////////////////////////////////////////////////////////////
    // 7. EDITAR FICHA (MODAL IDÉNTICO AL MÓDULO DE FICHAS)
    //    Reutiliza el mismo HTML modal y el mismo endpoint ficha/actualizar.
    //    Las cascadas se gestionan aquí para no cargar fichas_datatable.js
    //    (que inicializaría #tablaFichas, inexistente en esta vista).
    // ///////////////////////////////////////////////////////////////////

    // 7.1 Cascada: Municipio → Parroquia (para el modal de edición de ficha)
    $('#editar_municipio_id').on('change', function () {
        const municipioId = $(this).val();
        const $sel        = $('#editar_parroquia_id');
        if (!municipioId) {
            $sel.html('<option value="">-- Seleccione municipio --</option>').trigger('change');
            return;
        }
        despachoCargarParroquias(municipioId, $sel, null);
    });

    function despachoCargarParroquias(municipioId, $sel, valorActual) {
        $.get(`index.php?url=ficha/obtenerParroquiasPorMunicipio&municipio_id=${municipioId}`, function (data) {
            let opts = '<option value="">-- Seleccione parroquia --</option>';
            data.forEach(p => {
                const seleccionado = valorActual == p.id ? 'selected' : '';
                opts += `<option value="${p.id}" ${seleccionado}>${escapeHTML(p.nombre_parroquia)}</option>`;
            });
            $sel.prop('disabled', false).html(opts).trigger('change');
        }, 'json');
    }

    // 7.2 Cascada: Tipo de Emergencia → Casos específicos
    $('#editar_tipo_emergencia_id').on('change', function () {
        const tipoId = $(this).val();
        const $sel   = $('#editar_caso_id');
        if (!tipoId) {
            $sel.html('<option value="">-- Seleccione tipo primero --</option>').trigger('change');
            return;
        }
        despachoCargarCasos(tipoId, $sel, null);
    });

    function despachoCargarCasos(tipoId, $sel, valorActual) {
        $.get(`index.php?url=ficha/obtenerCasosPorTipo&tipo_id=${tipoId}`, function (data) {
            let opts = '<option value="">-- Seleccione caso --</option>';
            data.forEach(c => {
                const seleccionado = valorActual == c.id ? 'selected' : '';
                opts += `<option value="${c.id}" ${seleccionado}>${escapeHTML(c.nombre_caso)}</option>`;
            });
            $sel.prop('disabled', false).html(opts).trigger('change');
        }, 'json');
    }

    // 7.3 Apertura del modal de edición: carga datos completos de la ficha
    $(document).on('click', '#btnEditarFichaDesdeDetalle', function () {
        const fichaId = $(this).data('ficha-id');

        // Cerrar el modal de gestión antes de abrir el de edición
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleDespacho')).hide();

        document.getElementById('modalDetalleDespacho').addEventListener('hidden.bs.modal', function openEdit() {
            document.getElementById('modalDetalleDespacho').removeEventListener('hidden.bs.modal', openEdit);

            // Obtener datos completos de la ficha (mismo endpoint que fichas_datatable.js)
            $.get(`index.php?url=ficha/detalle&id=${fichaId}`, function (res) {
                if (!res.success || !res.data) {
                    Swal.fire('Error', 'No se pudo cargar la ficha.', 'error');
                    return;
                }
                const f = res.data;

                // Pre-llenar todos los campos operacionales del modal
                $('#editar_ficha_id').val(f.id);
                $('#editarFichaIdLabel').text(`#${f.id}`);
                $('#editar_telefono1').val(f.telefono1);
                $('#editar_telefono2').val(f.telefono2 || '');
                $('#editar_descripcion_caso').val(f.descripcion_caso);
                $('#editar_direccion_exacta').val(f.direccion_exacta);
                
                // Set hidden parroquia_id for cascade
                $('#editar_despacho_parroquia_id').val(f.parroquia_id);

                // Inicializar select2 en los nuevos selects
                $('#editar_despacho_comuna_id, #editar_despacho_sector_id').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalEditarFicha')
                });

                // Cargar cascadas de Comuna y Sector
                if (f.parroquia_id) {
                    despachoCargarComunas(f.parroquia_id, $('#editar_despacho_comuna_id'), f.comuna_id);
                }
                if (f.comuna_id) {
                    despachoCargarSectores(f.comuna_id, $('#editar_despacho_sector_id'), f.sector_id);
                }

                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarFicha')).show();
            }, 'json');
        }, { once: true });
    });

    // 7.4 Cascada: Comunas y Sectores en modal de Despacho
    $('#editar_despacho_comuna_id').on('change', function () {
        const comunaId = $(this).val();
        const $sel = $('#editar_despacho_sector_id');
        if (!comunaId) { $sel.prop('disabled', true).html('<option value="">-- Seleccionar Sector --</option>').trigger('change'); return; }
        despachoCargarSectores(comunaId, $sel, null);
    });

    function despachoCargarComunas(parroquiaId, $sel, valorActual) {
        $.get(`index.php?url=ficha/obtenerCatalogo&cat=comuna&estado=1&parroquia_id=${parroquiaId}`, function (res) {
            let opts = '<option value="">-- Seleccione comuna --</option>';
            if (res.data) {
                res.data.forEach(c => {
                    const sel = valorActual == c.id ? 'selected' : '';
                    opts += `<option value="${c.id}" ${sel}>${escapeHTML(c.nombre_comuna)}</option>`;
                });
            }
            $sel.prop('disabled', false).html(opts).trigger('change');
        }, 'json');
    }

    function despachoCargarSectores(comunaId, $sel, valorActual) {
        $.get(`index.php?url=ficha/obtenerCatalogo&cat=sector&estado=1&comuna_id=${comunaId}`, function (res) {
            let opts = '<option value="">-- Seleccione sector --</option>';
            if (res.data) {
                res.data.forEach(s => {
                    const sel = valorActual == s.id ? 'selected' : '';
                    opts += `<option value="${s.id}" ${sel}>${escapeHTML(s.nombre_sector)}</option>`;
                });
            }
            $sel.prop('disabled', false).html(opts).trigger('change');
        }, 'json');
    }

    // 7.5 Guardar edición: usa el endpoint operacional de despacho
    $('#btnGuardarEdicionFicha').on('click', function () {
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const fichaId = $('#editar_ficha_id').val();
        const datos   = new FormData(document.getElementById('formEditarFicha'));

        $.ajax({
            url:         'index.php?url=despacho/editarFicha',
            method:      'POST',
            data:         datos,
            processData: false,
            contentType: false,
            dataType:    'json',
            success: function (res) {
                if (res.success) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarFicha')).hide();
                    Swal.fire({ title: '¡Guardado!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });

                    // Al cerrar el modal de edición, volver al modal de gestión con datos frescos
                    document.getElementById('modalEditarFicha').addEventListener('hidden.bs.modal', function reopen() {
                        document.getElementById('modalEditarFicha').removeEventListener('hidden.bs.modal', reopen);
                        abrirModalGestion(parseInt(fichaId));
                    }, { once: true });

                    tablaDespachos.ajax.reload(null, false);
                } else {
                    Swal.fire('Error de Validación', res.message, 'error');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'),
            complete: () => btn.prop('disabled', false).html(originalText),
        });
    });





    // ///////////////////////////////////////////////////////////////////
    // 6. APERTURA DEL MODAL DE ASIGNACIÓN DESDE EL MODAL DE GESTIÓN
    // ///////////////////////////////////////////////////////////////////

    $(document).on('click', '#btnAgregarOrganismoDesdeDetalle', function () {
        const fichaId = $(this).data('ficha-id');
        const sectorId = $(this).data('sector-id') || 0;

        // Pre-inyectar el ficha_id, sector_id en el formulario y limpiar
        $('#asignar_ficha_id').val(fichaId);
        $('#formAsignarDespacho').data('sector-id', sectorId);
        $('#asignarFichaContextLabel').text(`— Ficha #${fichaId}`);
        $('#formAsignarDespacho')[0].reset();
        $('#asignar_ficha_id').val(fichaId); // Re-inyectar tras reset

        // Inicializar Select2 y cargar organismos
        inicializarSelect2Modal('#modalAsignarDespacho');
        cargarOrganismos('#asignar_organismo_id');

        // Limpiar y deshabilitar selector de cuadrante al abrir
        $('#asignar_cuadrante_id').prop('disabled', true).html('<option value="">-- Seleccione cuadrante --</option>').trigger('change.select2');

        // Cerrar el modal de gestión antes de abrir el de asignación
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleDespacho')).hide();

        document.getElementById('modalDetalleDespacho').addEventListener('hidden.bs.modal', function openAsignar() {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsignarDespacho')).show();
            document.getElementById('modalDetalleDespacho').removeEventListener('hidden.bs.modal', openAsignar);
        }, { once: true });
    });

    // Cascada dinámica: Organismo -> Cuadrante de Paz
    $('#asignar_organismo_id').on('change', function () {
        const organismoId = $(this).val();
        const sectorId = $('#formAsignarDespacho').data('sector-id') || 0;
        const sel = $('#asignar_cuadrante_id');
        if (!organismoId) {
            sel.prop('disabled', true).html('<option value="">-- Seleccione cuadrante --</option>').trigger('change.select2');
            return;
        }

        // Intentar primero buscar cuadrantes asignados a este organismo en el sector de la ficha
        let url = `index.php?url=ficha/obtenerCatalogo&cat=cuadrante&estado=1&organismo_id=${organismoId}`;
        if (sectorId > 0) {
            url += `&sector_id=${sectorId}`;
        }

        $.get(url, function (res) {
            let opts = '<option value="">-- Seleccione cuadrante --</option>';
            let count = 0;
            let lastId = null;
            if (res.data && res.data.length > 0) {
                res.data.forEach(c => {
                    opts += `<option value="${c.id}">${escapeHTML(c.nombre_cuadrante)} (${escapeHTML(c.nombre_sector)})</option>`;
                    lastId = c.id;
                    count++;
                });
            }

            // Si no se encontraron cuadrantes en el sector específico de la ficha, cargamos todos los del organismo
            if (count === 0 && sectorId > 0) {
                $.get(`index.php?url=ficha/obtenerCatalogo&cat=cuadrante&estado=1&organismo_id=${organismoId}`, function (res2) {
                    let opts2 = '<option value="">-- Seleccione cuadrante --</option>';
                    let count2 = 0;
                    let lastId2 = null;
                    if (res2.data) {
                        res2.data.forEach(c => {
                            opts2 += `<option value="${c.id}">${escapeHTML(c.nombre_cuadrante)} (${escapeHTML(c.nombre_sector)})</option>`;
                            lastId2 = c.id;
                            count2++;
                        });
                    }
                    sel.prop('disabled', count2 === 0).html(opts2).trigger('change.select2');
                    if (count2 === 1) {
                        sel.val(lastId2).trigger('change.select2');
                    }
                }, 'json');
            } else {
                sel.prop('disabled', count === 0).html(opts).trigger('change.select2');
                if (count === 1) {
                    sel.val(lastId).trigger('change.select2');
                }
            }
        }, 'json');
    });

    // ///////////////////////////////////////////////////////////////////
    // 7. PERSISTENCIA DEL DESPACHO (GUARDAR)
    // ///////////////////////////////////////////////////////////////////

    $('#btnGuardarDespacho').on('click', function () {
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const fichaId = $('#asignar_ficha_id').val();
        const datos   = new FormData(document.getElementById('formAsignarDespacho'));

        $.ajax({
            url:         'index.php?url=despacho/guardar',
            method:      'POST',
            data:         datos,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsignarDespacho')).hide();
                    Swal.fire({ title: '¡Despachado!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });

                    // Reabrir el modal de gestión para mostrar el nuevo organismo asignado
                    document.getElementById('modalAsignarDespacho').addEventListener('hidden.bs.modal', function reopen() {
                        abrirModalGestion(parseInt(fichaId));
                        document.getElementById('modalAsignarDespacho').removeEventListener('hidden.bs.modal', reopen);
                    }, { once: true });

                    tablaDespachos.ajax.reload(null, false);
                } else {
                    Swal.fire('Error de Validación', res.message, 'error');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'),
            complete: () => btn.prop('disabled', false).html(originalText),
        });
    });

    // ///////////////////////////////////////////////////////////////////
    // 8. FUNCIONES AUXILIARES
    // ///////////////////////////////////////////////////////////////////

    /**
     * Carga organismos activos en el selector especificado.
     */
    function cargarOrganismos(selectorId) {
        $.get('index.php?url=despacho/obtenerOrganismos', function (data) {
            let opts = '<option value="">-- Seleccione organismo --</option>';
            data.forEach(o => {
                opts += `<option value="${o.id}">${escapeHTML(o.nombre_organismo)}</option>`;
            });
            $(selectorId).html(opts).trigger('change.select2');
        }, 'json');
    }

    /**
     * Inicializa Select2 dentro de un modal Bootstrap (z-index fix).
     */
    function inicializarSelect2Modal(modalSelector) {
        const $modal = $(modalSelector);
        $modal.find('.form-select').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $modal,
                    width: '100%',
                    language: 'es',
                });
            }
        });
    }

});
