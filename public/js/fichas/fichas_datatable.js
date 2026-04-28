/**
 * fichas_datatable.js - Gestión de Fichas de Emergencia (Operaciones)
 * 
 * Controla el ciclo de vida completo de las fichas: creación, edición, 
 * visualización detallada y flujo de estados. Implementa procesamiento 
 * en servidor, cascadas dinámicas para ubicación/casos y validación de RBAC.
 */

$(document).ready(function () {

    // 1. CONFIGURACIÓN INICIAL Y UI (SELECT2 & PERMISOS)
    
    // Inicialización de Select2 con soporte para modales (z-index fix)
    $('.form-select').each(function () {
        const $modal = $(this).closest('.modal');
        $(this).select2({
            theme: 'bootstrap-5',
            dropdownParent: $modal.length ? $modal : $(document.body),
            width: '100%',
            language: 'es'
        });
    });

    const tablaEl      = $('#tablaFichas');
    const estadoFiltro = tablaEl.data('estado') || 'todos';
    

    // Inyección de banderas de permisos desde el scope global
    const puedeEditar = window.VEN911_PERM_EDITAR ?? false;


    // Diccionarios visuales para estados de fichas
    const badgeClases = {
        'Pendiente':  'badge-pendiente',
        'En Proceso': 'badge-en-proceso',
        'Atendido':   'badge-atendido',
        'Cerrado':    'badge-cerrado',
    };

    const iconosEstado = {
        'Pendiente':  'bi-hourglass-split',
        'En Proceso': 'bi-arrow-repeat',
        'Atendido':   'bi-check-circle-fill',
        'Cerrado':    'bi-lock-fill',
    };


    // 2. INICIALIZACIÓN DE DATATABLE DE FICHAS (SERVER-SIDE)
    const tablaFichas = tablaEl.DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url:  `index.php?url=ficha/obtenerDatos&estado=${estadoFiltro}`,
            type: 'POST',
            error: function () {
                Swal.fire('Error', 'No se pudo cargar la tabla de fichas.', 'error');
            },
        },
        columns: [
            {
                // Columna: Contador de registros
                data: null,
                width: '50px',
                orderable: false,
                searchable: false,
                render: (d, type, row, meta) => `<span class="fw-bold text-secondary">${meta.row + meta.settings._iDisplayStart + 1}</span>`
            },
            {
                // Columna: Solicitante (Sanitizado)
                data: 'nombre_solicitante',
                render: (d) => escapeHTML(d)
            },
            {
                // Columna: Caso y Tipo de Emergencia
                data: 'nombre_caso',
                render: (d, type, row) => `
                    <div class="fw-semibold">${escapeHTML(d)}</div>
                    <small class="text-muted">${escapeHTML(row.tipo_emergencia)}</small>`
            },
            {
                // Columna: Ubicación Geográfica (Parroquia/Municipio)
                data: 'nombre_parroquia',
                render: (d, type, row) => `
                    <div>${escapeHTML(d)}</div>
                    <small class="text-muted">${escapeHTML(row.nombre_municipio)}</small>`
            },
            {
                // Columna: Estado Operativo (Badge dinámico)
                data: 'estado_ficha',
                render: (d) => {
                    const cls  = badgeClases[d]  || 'badge-cerrado';

                    const icon = iconosEstado[d] || 'bi-question-circle';
                    return `<span class="badge-ficha-estado ${cls}"><i class="bi ${icon}"></i>${escapeHTML(d)}</span>`;
                }
            },
            {
                // Columna: Marca de tiempo de creación
                data: 'fecha_creacion',
                render: (d) => `<small class="text-muted">${d}</small>`
            },
            {
                // Columna: Acciones (Control de permisos dinámico)
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: (d, type, row) => {
                    let btns = `
                        <button class="btn btn-ven-detalle btn-accion btn-ver-detalle me-1"
                                data-id="${row.id}" title="Ver detalle" id="btnDetalle-${row.id}">
                            <i class="bi bi-eye-fill"></i>
                        </button>`;
                    
                    // Solo permitir edición en estados no terminales
                    if (puedeEditar && row.estado_ficha !== 'Cerrado' && row.estado_ficha !== 'Atendido') {

                        btns += `
                        <button class="btn btn-ven-edit btn-accion btn-editar-ficha"
                                data-id="${row.id}" title="Editar" id="btnEditar-${row.id}">
                            <i class="bi bi-pencil-fill"></i>
                        </button>`;
                    }
                    return btns;
                }
            }
        ],
        language:   window.Ven911DataTablesLang,
        order:      [[5, 'desc']], // Ordenar por fecha de creación (desc)
        responsive: true,
        pageLength: 15,
    });

    // 3. GESTIÓN DE FORMULARIOS (CREACIÓN)
    $('#btnNuevaFicha').on('click', function () {
        $('#formCrearFicha')[0].reset();
        // Reset de cascadas
        $('#crear_parroquia_id').prop('disabled', true).html('<option value="">-- Primero seleccione municipio --</option>');
        $('#crear_caso_id').prop('disabled', true).html('<option value="">-- Primero seleccione tipo --</option>');
        new bootstrap.Modal(document.getElementById('modalCrearFicha')).show();
    });

    // Persistencia de nueva ficha (AJAX)
    $('#btnGuardarFicha').on('click', function () {
        const datos = new FormData(document.getElementById('formCrearFicha'));
        $.ajax({
            url:         'index.php?url=ficha/guardar',
            method:      'POST',
            data:         datos,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCrearFicha')).hide();
                    Swal.fire('¡Registrada!', res.message, 'success');
                    tablaFichas.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'),
        });
    });

    // 4. LÓGICA DE CASCADAS DINÁMICAS (AJAX POLLING)

    // Cascada: Municipios -> Parroquias (Soporta Crear/Editar)
    $('#crear_municipio_id').on('change', function () {
        const municipioId = $(this).val();
        const sel = $('#crear_parroquia_id');
        if (!municipioId) { sel.prop('disabled', true).html('<option value="">-- Primero seleccione municipio --</option>').trigger('change'); return; }
        cargarParroquias(municipioId, sel, null);
    });

    $('#editar_municipio_id').on('change', function () {
        const municipioId = $(this).val();
        const sel = $('#editar_parroquia_id');
        if (!municipioId) { sel.html('<option value="">-- Seleccione municipio --</option>').trigger('change'); return; }
        cargarParroquias(municipioId, sel, null);
    });

    function cargarParroquias(municipioId, $sel, valorActual) {
        $.get(`index.php?url=ficha/obtenerParroquiasPorMunicipio&municipio_id=${municipioId}`, function (data) {
            let opts = '<option value="">-- Seleccione parroquia --</option>';
            data.forEach(p => {
                const sel = valorActual == p.id ? 'selected' : '';
                opts += `<option value="${p.id}" ${sel}>${escapeHTML(p.nombre_parroquia)}</option>`;
            });
            $sel.prop('disabled', false).html(opts).trigger('change');
        }, 'json');
    }

    // Cascada: Tipo Emergencia -> Casos (Soporta Crear/Editar)
    $('#crear_tipo_emergencia_id').on('change', function () {
        const tipoId = $(this).val();
        const sel = $('#crear_caso_id');
        if (!tipoId) { sel.prop('disabled', true).html('<option value="">-- Primero seleccione tipo --</option>').trigger('change'); return; }
        cargarCasos(tipoId, sel, null);
    });

    $('#editar_tipo_emergencia_id').on('change', function () {
        const tipoId = $(this).val();
        const sel = $('#editar_caso_id');
        if (!tipoId) { sel.html('<option value="">-- Seleccione tipo --</option>').trigger('change'); return; }
        cargarCasos(tipoId, sel, null);
    });

    function cargarCasos(tipoId, $sel, valorActual) {
        $.get(`index.php?url=ficha/obtenerCasosPorTipo&tipo_id=${tipoId}`, function (data) {
            let opts = '<option value="">-- Seleccione caso --</option>';
            data.forEach(c => {
                const sel = valorActual == c.id ? 'selected' : '';
                opts += `<option value="${c.id}" ${sel}>${escapeHTML(c.nombre_caso)}</option>`;
            });
            $sel.prop('disabled', false).html(opts).trigger('change');
        }, 'json');
    }

    // 5. VISUALIZACIÓN DE DETALLES Y GESTIÓN DE FLUJO (ESTADOS)

    // Carga asíncrona de la "Ficha de Vida" del incidente
    $(document).on('click', '.btn-ver-detalle', function () {
        const fichaId = $(this).data('id');
        $('#detalleFichaIdLabel').text(`#${fichaId}`);
        $('#contenidoDetalleFicha').html('<div class="text-center py-4"><div class="spinner-border text-success"></div></div>');


        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleFicha')).show();

        $.get(`index.php?url=ficha/detalle&id=${fichaId}`, function (res) {
            if (!res.success || !res.data) {
                $('#contenidoDetalleFicha').html('<p class="text-danger text-center">No se pudo cargar el detalle.</p>');
                return;
            }
            const f = res.data;
            const badgeCls = badgeClases[f.estado_ficha] || 'badge-cerrado';


            // Renderizado de la cuadrícula de información detallada
            $('#contenidoDetalleFicha').html(`
                <div class="mb-3">
                    <span class="badge-ficha-estado ${badgeCls} fs-6 mb-3 d-inline-block">
                        <i class="bi ${iconosEstado[f.estado_ficha] || 'bi-question-circle'}"></i> ${escapeHTML(f.estado_ficha)}
                    </span>
                </div>
                <div class="ficha-detalle-grid">
                    <div class="ficha-detalle-item"><label>Solicitante</label><span>${escapeHTML(f.nombre_solicitante)}</span></div>
                    <div class="ficha-detalle-item"><label>Cédula</label><span>${f.cedula_solicitante ? 'V-' + escapeHTML(f.cedula_solicitante) : '<em class="text-muted">S/D</em>'}</span></div>
                    <div class="ficha-detalle-item"><label>Teléfono 1</label><span>${escapeHTML(f.telefono1)}</span></div>
                    <div class="ficha-detalle-item"><label>Teléfono 2</label><span>${f.telefono2 ? escapeHTML(f.telefono2) : '<em class="text-muted">N/A</em>'}</span></div>
                    <div class="ficha-detalle-item"><label>Municipio</label><span>${escapeHTML(f.nombre_municipio)}</span></div>
                    <div class="ficha-detalle-item"><label>Parroquia</label><span>${escapeHTML(f.nombre_parroquia)}</span></div>
                    <div class="ficha-detalle-item" style="grid-column:1/-1"><label>Dirección</label><span>${escapeHTML(f.direccion_exacta)}</span></div>
                    <div class="ficha-detalle-item"><label>Tipo de Emergencia</label><span>${escapeHTML(f.tipo_emergencia)}</span></div>
                    <div class="ficha-detalle-item"><label>Caso</label><span>${escapeHTML(f.nombre_caso)}</span></div>
                    <div class="ficha-detalle-item" style="grid-column:1/-1"><label>Descripción</label><span>${escapeHTML(f.descripcion_caso)}</span></div>
                    <div class="ficha-detalle-item"><label>Fecha Creación</label><span>${escapeHTML(f.fecha_creacion)}</span></div>
                    <div class="ficha-detalle-item"><label>Creado por</label><span>${escapeHTML(f.nombre_creador || 'Sistema')}</span></div>
                </div>
            `);
        }, 'json');
    });



    // 6. GESTIÓN DE EDICIÓN



    // Carga de datos para edición
    $(document).on('click', '.btn-editar-ficha', function () {
        const fichaId = $(this).data('id');
        $.get(`index.php?url=ficha/detalle&id=${fichaId}`, function (res) {
            if (!res.success || !res.data) { Swal.fire('Error', 'No se pudo cargar la ficha.', 'error'); return; }
            const f = res.data;

            $('#editar_ficha_id').val(f.id);
            $('#editarFichaIdLabel').text(`#${f.id}`);
            $('#editar_cedula_solicitante').val(f.cedula_solicitante || '');
            $('#editar_nombre_solicitante').val(f.nombre_solicitante);
            $('#editar_telefono1').val(f.telefono1);
            $('#editar_telefono2').val(f.telefono2 || '');
            $('#editar_municipio_id').val(f.municipio_id).trigger('change.select2');
            $('#editar_tipo_emergencia_id').val(f.tipo_emergencia_id).trigger('change.select2');
            $('#editar_descripcion_caso').val(f.descripcion_caso);
            $('#editar_direccion_exacta').val(f.direccion_exacta);

            // Re-hidratación de cascadas en el modal de edición
            cargarParroquias(f.municipio_id, $('#editar_parroquia_id'), f.parroquia_id);
            cargarCasos(f.tipo_emergencia_id, $('#editar_caso_id'), f.caso_id);

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarFicha')).show();
        }, 'json');
    });

    // Persistencia de edición (AJAX)
    $('#btnGuardarEdicion').on('click', function () {
        const datos = new FormData(document.getElementById('formEditarFicha'));
        $.ajax({
            url:         'index.php?url=ficha/actualizar',
            method:      'POST',
            data:         datos,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarFicha')).hide();
                    Swal.fire('¡Guardado!', res.message, 'success');
                    tablaFichas.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'),
        });
    });

});
