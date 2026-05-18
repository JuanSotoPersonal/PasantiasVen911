/**
 * fichas_configuracion.js - Gestión de Catálogos y Parámetros del Sistema
 * 
 * Centraliza las operaciones ABMC (Alta, Baja, Modificación y Consulta) de los
 * componentes maestros: Tipos de Emergencia, Casos, Geografía (Municipios/Parroquias)
 * y Organismos. Implementa lógica de "Papelera de Reciclaje" mediante estados.
 */

$(function () {

    // 1. CONFIGURACIÓN INICIAL Y UI (SELECT2)
    const lang = window.Ven911DataTablesLang;

    // Inicialización dinámica de Select2 en modales para evitar conflictos de z-index
    $('.form-select').each(function () {
        const $modal = $(this).closest('.modal');
        $(this).select2({
            theme: 'bootstrap-5',
            dropdownParent: $modal.length ? $modal : $(document.body),
            width: '100%',
            language: 'es'
        });
    });

    // 2. HELPERS Y UTILIDADES DE GESTIÓN (CRUD CORE)
    
    // Sincronización de contadores de registros inactivos en las pestañas
    function actualizarContadoresInactivos() {
        let total = 0;
        $('.count-inactivo').each(function() {
            total += parseInt($(this).text()) || 0;
        });
    }

    // Hook para actualizar contadores tras cada recarga de DataTable
    function setupContadorDT(dt, elementId) {
        dt.on('xhr.dt', function(e, settings, json) {
            const count = (json && json.data) ? json.data.length : 0;
            $(`#${elementId}`).text(count).addClass('count-inactivo');
            actualizarContadoresInactivos();
        });
    }

    /**
     * Motor de persistencia centralizado para catálogos.
     * Gestiona el envío asíncrono, cierre de modales y recarga de múltiples tablas.
     */
    function guardarCatalogo(datos, tablasRef, modalId, callback) {
        return $.post('index.php?url=ficha/guardarCatalogo', datos, function (res) {
            if (res.success) {
                if (modalId) {
                    const m = document.getElementById(modalId);
                    if (m) bootstrap.Modal.getInstance(m)?.hide();
                }
                Swal.fire({ icon: 'success', title: '¡Operación Exitosa!', text: res.message, timer: 1500, showConfirmButton: false });
                
                // Recarga atómica de tablas vinculadas
                if (Array.isArray(tablasRef)) {
                    tablasRef.forEach(t => {
                        if (t && typeof t.ajax === 'object') t.ajax.reload(null, false);
                    });
                } else if (tablasRef && typeof tablasRef.ajax === 'object') {
                    tablasRef.ajax.reload(null, false);
                }
                
                if (callback) callback();
            } else {
                Swal.fire('Error', res.message || 'No se pudo completar la operación.', 'error');
            }
        }, 'json').fail(() => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'));
    }

    // Renderizador de badges de estado con soporte para Toggle interactivo
    function renderEstadoBadge(row, catalogo) {
        const isActivo = row.estado == 1;
        const badgeClass = isActivo ? 'badge-activo' : 'badge-inactivo';
        const icon = isActivo ? 'bi-toggle-on' : 'bi-toggle-off';
        const text = isActivo ? 'Activo' : 'Inactivo';

        return `
            <button type="button" class="btn-toggle-estado btn-toggle-cat-estado" 
                    data-id="${row.id}" data-cat="${catalogo}" data-estado="${row.estado}"
                    title="Clic para cambiar estado">
                <span class="badge badge-estado ${badgeClass}">
                    <i class="bi ${icon} me-1"></i>${text}
                </span>
            </button>`;
    }

    // Gestión de diálogos de confirmación para cambios de estado (Baja Lógica)
    function confirmarEstado(id, catalogo, tablasRef, activando = false) {
        const title = activando ? '¿Reactivar registro?' : '¿Inhabilitar registro?';
        const text  = activando ? 'El registro volverá a estar disponible en el sistema.' : 'El registro se moverá a la papelera y no será visible en los selectores.';
        const icon  = activando ? 'info' : 'warning';
        const btn   = activando ? 'Sí, reactivar' : 'Sí, inhabilitar';

        Swal.fire({
            title, text, icon,
            showCancelButton: true,
            confirmButtonText: btn,
            cancelButtonText: 'Cancelar',
        }).then(result => {
            if (!result.isConfirmed) return;
            guardarCatalogo({ catalogo, accion: 'eliminar', id }, tablasRef);
        });
    }

    // Generador dinámico de botones de acción según el estado del registro
    function btnsAccion(row, catalogo) {
        const isActivo = (row.estado == 1);
        
        if (!isActivo) {
            return `
                <button class="btn btn-ven-primary btn-accion btn-toggle-cat-estado"
                        data-id="${row.id}" data-cat="${catalogo}" data-estado="0"
                        title="Reactivar registro">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>`;
        }

        return `
            <button class="btn btn-ven-edit btn-accion btn-editar-cat me-1"
                    data-cat="${catalogo}" data-row='${escapeHTML(JSON.stringify(row))}' title="Editar">
                <i class="bi bi-pencil-fill"></i>
            </button>`;
    }

    // 3. MÓDULO: TIPOS DE EMERGENCIA
    const dtTipos = $('#tablaTipos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=tipo_emergencia&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'tipo_emergencia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'tipo_emergencia') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtTiposInactivos = $('#tablaTiposInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=tipo_emergencia&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'tipo_emergencia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'tipo_emergencia') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtTiposInactivos, 'count-inactivos-tipos');

    $('#btnNuevoTipo').on('click', () => {
        $('#cat_simple_catalogo').val('tipo_emergencia');
        $('#cat_simple_accion').val('crear');
        $('#cat_simple_id').val('0');
        $('#cat_simple_label').text('Nombre del Tipo');
        $('#cat_simple_valor').val('');
        $('#cat_simple_descripcion').val(''); 
        $('#modalCatalogoSimpleTitulo').text('Nuevo Tipo de Emergencia');
        new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
    });

    // Acción unificada para catálogos simples (Nombre + Descripción)
    $('#btnGuardarCatSimple').on('click', function () {
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const datos = Object.fromEntries(new FormData(document.getElementById('formCatalogoSimple')));
        const catalogo = datos.catalogo;
        
        // Normalización de llaves para el backend
        if (catalogo === 'municipio') datos.nombre_municipio = datos.nombre;
        if (catalogo === 'organismo') datos.nombre_organismo = datos.nombre;
        
        const tablas = {
            tipo_emergencia: [dtTipos, dtTiposInactivos], 
            municipio: [dtMunicipios, dtMunicipiosInactivos], 
            organismo: [dtOrganismos, dtOrganismosInactivos],
            motivo_cierre: [
                dtMotivos, dtMotivosInactivos,
                ...(typeof dtMotivosOrg !== 'undefined' && dtMotivosOrg ? [dtMotivosOrg] : []),
                ...(typeof dtMotivosOrgInactivos !== 'undefined' && dtMotivosOrgInactivos ? [dtMotivosOrgInactivos] : [])
            ]
        };
        guardarCatalogo(datos, tablas[catalogo], 'modalCatalogoSimple').always(() => btn.prop('disabled', false).html(originalText));
    });

    // 4. MÓDULO: CASOS DE EMERGENCIA
    const dtCasos = $('#tablaCasos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=caso&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_caso', render: (d) => escapeHTML(d) },
            { data: 'tipo_emergencia', render: (d) => `<span class="badge bg-secondary">${escapeHTML(d)}</span>` },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'caso'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'caso') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtCasosInactivos = $('#tablaCasosInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=caso&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_caso', render: (d) => escapeHTML(d) },
            { data: 'tipo_emergencia', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'caso'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'caso') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtCasosInactivos, 'count-inactivos-casos');

    $('#filtroCasoTipo').on('change', function () {
        const tipoId = $(this).val();
        dtCasos.ajax.url(`index.php?url=ficha/obtenerCatalogo&cat=caso${tipoId ? '&tipo_id=' + tipoId : ''}`).load();
    });

    $('#btnNuevoCaso').on('click', () => {
        $('#formCaso')[0].reset();
        $('#caso_accion').val('crear');
        $('#caso_id').val('0');
        $('#modalCasoTitulo').text('Nuevo Caso');
        new bootstrap.Modal(document.getElementById('modalCaso')).show();
    });

    $('#btnGuardarCaso').on('click', function () {
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const datos = Object.fromEntries(new FormData(document.getElementById('formCaso')));
        guardarCatalogo(datos, [dtCasos, dtCasosInactivos], 'modalCaso').always(() => btn.prop('disabled', false).html(originalText));
    });



    // 5. MÓDULO: MUNICIPIOS
    const dtMunicipios = $('#tablaMunicipios').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=municipio&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'municipio'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'municipio') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtMunicipiosInactivos = $('#tablaMunicipiosInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=municipio&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'municipio'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'municipio') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtMunicipiosInactivos, 'count-inactivos-municipios');

    $('#btnNuevoMunicipio').on('click', () => {
        $('#cat_simple_catalogo').val('municipio');
        $('#cat_simple_accion').val('crear');
        $('#cat_simple_id').val('0');
        $('#cat_simple_label').text('Nombre del Municipio');
        $('#cat_simple_valor').val('');
        $('#cat_simple_descripcion').val(''); 
        $('#modalCatalogoSimpleTitulo').text('Nuevo Municipio');
        new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
    });

    // 6. MÓDULO: PARROQUIAS
    const dtParroquias = $('#tablaParroquias').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=parroquia&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_parroquia', render: (d) => escapeHTML(d) },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'parroquia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'parroquia') },
        ],
        language: lang, pageLength: 10, order: [[2, 'asc'], [1, 'asc']],
    });

    const dtParroquiasInactivos = $('#tablaParroquiasInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=parroquia&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_parroquia', render: (d) => escapeHTML(d) },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'parroquia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'parroquia') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtParroquiasInactivos, 'count-inactivos-parroquias');

    $('#filtroParroquiaMunicipio').on('change', function () {
        const municipioId = $(this).val();
        dtParroquias.ajax.url(`index.php?url=ficha/obtenerCatalogo&cat=parroquia${municipioId ? '&municipio_id=' + municipioId : ''}`).load();
    });

    $('#btnNuevaParroquia').on('click', () => {
        $('#formParroquia')[0].reset();
        $('#parroquia_accion').val('crear');
        $('#parroquia_id').val('0');
        $('#modalParroquiaTitulo').text('Nueva Parroquia');
        new bootstrap.Modal(document.getElementById('modalParroquia')).show();
    });

    $('#btnGuardarParroquia').on('click', function () {
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const datos = Object.fromEntries(new FormData(document.getElementById('formParroquia')));
        guardarCatalogo(datos, [dtParroquias, dtParroquiasInactivos], 'modalParroquia').always(() => btn.prop('disabled', false).html(originalText));
    });

    // Helper para cargar selects asincrónicamente
    function cargarSelectCascada(url, selectId, valueField, textField, textLabel, selectedId = null) {
        const $select = $(`#${selectId}`);
        $select.html('<option value="">Cargando...</option>').prop('disabled', true);
        $.getJSON(url, function(res) {
            $select.empty().append(`<option value="">-- Seleccione ${textLabel} --</option>`);
            if (res.data) {
                res.data.forEach(item => {
                    const selected = (selectedId && item[valueField] == selectedId) ? 'selected' : '';
                    $select.append(`<option value="${item[valueField]}" ${selected}>${escapeHTML(item[textField])}</option>`);
                });
            }
            $select.prop('disabled', false).trigger('change.select2');
        });
    }

    // --- NUEVO MÓDULO: COMUNAS ---
    const dtComunas = $('#tablaComunas').length ? $('#tablaComunas').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=comuna&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_comuna', render: (d) => escapeHTML(d) },
            { data: 'nombre_parroquia', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'comuna'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'comuna') },
        ],
        language: lang, pageLength: 10, order: [[2, 'asc'], [1, 'asc']],
    }) : null;

    $('#btnNuevaComuna').on('click', () => {
        $('#formComuna')[0].reset();
        $('#comuna_accion').val('crear');
        $('#comuna_id').val('0');
        $('#modalComunaTitulo').text('Nueva Comuna');
        cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=parroquia&estado=1', 'comuna_parroquia_id', 'id', 'nombre_parroquia', 'Parroquia');
        new bootstrap.Modal(document.getElementById('modalComuna')).show();
    });

    $('#btnGuardarComuna').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        guardarCatalogo(Object.fromEntries(new FormData(document.getElementById('formComuna'))), [dtComunas], 'modalComuna').always(() => btn.prop('disabled', false).text('Guardar'));
    });

    // --- NUEVO MÓDULO: SECTORES ---
    const dtSectores = $('#tablaSectores').length ? $('#tablaSectores').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=sector&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_sector', render: (d) => escapeHTML(d) },
            { data: 'nombre_comuna', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'sector'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'sector') },
        ],
        language: lang, pageLength: 10, order: [[2, 'asc'], [1, 'asc']],
    }) : null;

    $('#btnNuevoSector').on('click', () => {
        $('#formSector')[0].reset();
        $('#sector_accion').val('crear');
        $('#sector_id').val('0');
        $('#modalSectorTitulo').text('Nuevo Sector');
        cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=comuna&estado=1', 'sector_comuna_id', 'id', 'nombre_comuna', 'Comuna');
        new bootstrap.Modal(document.getElementById('modalSector')).show();
    });

    $('#btnGuardarSector').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        guardarCatalogo(Object.fromEntries(new FormData(document.getElementById('formSector'))), [dtSectores], 'modalSector').always(() => btn.prop('disabled', false).text('Guardar'));
    });

    // --- NUEVO MÓDULO: CUADRANTES DE PAZ ---
    const dtCuadrantes = $('#tablaCuadrantes').length ? $('#tablaCuadrantes').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=cuadrante&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_cuadrante', render: (d) => escapeHTML(d) },
            { data: 'nombre_sector', render: (d) => escapeHTML(d) },
            { data: 'nombre_organismo', render: (d) => d ? `<span class="badge bg-info text-dark">${escapeHTML(d)}</span>` : '<span class="text-muted">Ninguno</span>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'cuadrante'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'cuadrante') },
        ],
        language: lang, pageLength: 10, order: [[2, 'asc'], [1, 'asc']],
    }) : null;

    $('#btnNuevoCuadrante').on('click', () => {
        $('#formCuadrante')[0].reset();
        $('#cuadrante_accion').val('crear');
        $('#cuadrante_id').val('0');
        $('#modalCuadranteTitulo').text('Nuevo Cuadrante');
        cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=sector&estado=1', 'cuadrante_sector_id', 'id', 'nombre_sector', 'Sector');
        cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=organismo&estado=1', 'cuadrante_organismo_id', 'id', 'nombre_organismo', 'Organismo');
        new bootstrap.Modal(document.getElementById('modalCuadrante')).show();
    });

    $('#btnGuardarCuadrante').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        guardarCatalogo(Object.fromEntries(new FormData(document.getElementById('formCuadrante'))), [dtCuadrantes], 'modalCuadrante').always(() => btn.prop('disabled', false).text('Guardar'));
    });

    // 7. MÓDULO: ORGANISMOS DE RESPUESTA
    const dtOrganismos = $('#tablaOrganismos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=organismo&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_organismo', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'organismo'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'organismo') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtOrganismosInactivos = $('#tablaOrganismosInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=organismo&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_organismo', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'organismo'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'organismo') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtOrganismosInactivos, 'count-inactivos-organismos');

    $('#btnNuevoOrganismo').on('click', () => {
        $('#cat_simple_catalogo').val('organismo');
        $('#cat_simple_accion').val('crear');
        $('#cat_simple_id').val('0');
        $('#cat_simple_label').text('Nombre del Organismo');
        $('#cat_simple_valor').val('');
        $('#cat_simple_descripcion').val(''); 
        $('#modalCatalogoSimpleTitulo').text('Nuevo Organismo de Respuesta');
        new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
    });

    // 8. MÓDULO: MOTIVOS DE CIERRE DE FICHA (contexto = ficha)
    const dtMotivos = $('#tablaMotivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=motivo_cierre&estado=1&contexto=ficha', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'motivo_cierre'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'motivo_cierre') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtMotivosInactivos = $('#tablaMotivosInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=motivo_cierre&estado=0&contexto=ficha', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'motivo_cierre'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'motivo_cierre') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtMotivosInactivos, 'count-inactivos-motivos');

    $('#btnNuevoMotivo').on('click', () => {
        $('#cat_simple_catalogo').val('motivo_cierre');
        $('#cat_simple_contexto').val('ficha');           // Contexto: cierre de ficha
        $('#cat_simple_accion').val('crear');
        $('#cat_simple_id').val('0');
        $('#cat_simple_label').text('Nombre del Motivo');
        $('#cat_simple_valor').val('');
        $('#cat_simple_descripcion').val(''); 
        $('#modalCatalogoSimpleTitulo').text('Nuevo Motivo de Cierre de Ficha');
        new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
    });

    // 8b. MÓDULO: MOTIVOS DE CANCELACIÓN DE ORGANISMO (contexto = organismo)
    const dtMotivosOrg = $('#tablaMotivosOrganismo').length ? $('#tablaMotivosOrganismo').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=motivo_cierre&estado=1&contexto=organismo', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'motivo_cierre'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'motivo_cierre') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    }) : null;

    const dtMotivosOrgInactivos = $('#tablaMotivosOrganismoInactivos').length ? $('#tablaMotivosOrganismoInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=motivo_cierre&estado=0&contexto=organismo', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'motivo_cierre'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, row) => btnsAccion(row, 'motivo_cierre') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    }) : null;
    if (dtMotivosOrgInactivos) setupContadorDT(dtMotivosOrgInactivos, 'count-inactivos-motivos-org');

    $('#btnNuevoMotivoOrganismo').on('click', () => {
        $('#cat_simple_catalogo').val('motivo_cierre');
        $('#cat_simple_contexto').val('organismo');       // Contexto: cancelación de organismo
        $('#cat_simple_accion').val('crear');
        $('#cat_simple_id').val('0');
        $('#cat_simple_label').text('Nombre del Motivo');
        $('#cat_simple_valor').val('');
        $('#cat_simple_descripcion').val(''); 
        $('#modalCatalogoSimpleTitulo').text('Nuevo Motivo de Cancelación de Organismo');
        new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
    });

    // 9. GESTIÓN DE EDICIÓN Y ESTADOS (DELEGACIÓN GLOBAL)

    // Carga dinámica de datos en modales según el tipo de catálogo
    $(document).on('click', '.btn-editar-cat', function () {
        const catalogo = $(this).data('cat');
        const row      = $(this).data('row');

        if (catalogo === 'tipo_emergencia') {
            $('#cat_simple_catalogo').val('tipo_emergencia');
            $('#cat_simple_accion').val('editar');
            $('#cat_simple_id').val(row.id);
            $('#cat_simple_label').text('Nombre del Tipo');
            $('#cat_simple_valor').val(row.nombre);
            $('#cat_simple_descripcion').val(row.descripcion || '');
            $('#modalCatalogoSimpleTitulo').text(`Editar Tipo de Emergencia #${row.id}`);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCatalogoSimple')).show();

        } else if (catalogo === 'caso') {
            $('#caso_accion').val('editar');
            $('#caso_id').val(row.id);
            $('#caso_tipo_id').val(row.tipo_emergencia_id).trigger('change.select2');
            $('#caso_nombre').val(row.nombre_caso);
            $('#caso_descripcion').val(row.descripcion || '');
            $('#modalCasoTitulo').text(`Editar Caso #${row.id}`);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCaso')).show();



        } else if (catalogo === 'municipio') {
            $('#cat_simple_catalogo').val('municipio');
            $('#cat_simple_accion').val('editar');
            $('#cat_simple_id').val(row.id);
            $('#cat_simple_label').text('Nombre del Municipio');
            $('#cat_simple_valor').val(row.nombre_municipio);
            $('#cat_simple_descripcion').val(row.descripcion || '');
            $('#modalCatalogoSimpleTitulo').text(`Editar Municipio #${row.id}`);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCatalogoSimple')).show();

        } else if (catalogo === 'parroquia') {
            $('#parroquia_accion').val('editar');
            $('#parroquia_id').val(row.id);
            $('#parroquia_municipio_id').val(row.municipio_id).trigger('change.select2');
            $('#parroquia_nombre').val(row.nombre_parroquia);
            $('#parroquia_descripcion').val(row.descripcion || '');
            $('#modalParroquiaTitulo').text(`Editar Parroquia #${row.id}`);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalParroquia')).show();

        } else if (catalogo === 'comuna') {
            $('#comuna_accion').val('editar');
            $('#comuna_id').val(row.id);
            $('#comuna_nombre').val(row.nombre_comuna);
            $('#comuna_descripcion').val(row.descripcion || '');
            $('#modalComunaTitulo').text(`Editar Comuna #${row.id}`);
            cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=parroquia&estado=1', 'comuna_parroquia_id', 'id', 'nombre_parroquia', 'Parroquia', row.parroquia_id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalComuna')).show();

        } else if (catalogo === 'sector') {
            $('#sector_accion').val('editar');
            $('#sector_id').val(row.id);
            $('#sector_nombre').val(row.nombre_sector);
            $('#sector_descripcion').val(row.descripcion || '');
            $('#modalSectorTitulo').text(`Editar Sector #${row.id}`);
            cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=comuna&estado=1', 'sector_comuna_id', 'id', 'nombre_comuna', 'Comuna', row.comuna_id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSector')).show();

        } else if (catalogo === 'cuadrante') {
            $('#cuadrante_accion').val('editar');
            $('#cuadrante_id').val(row.id);
            $('#cuadrante_nombre').val(row.nombre_cuadrante);
            $('#cuadrante_descripcion').val(row.descripcion || '');
            $('#modalCuadranteTitulo').text(`Editar Cuadrante #${row.id}`);
            cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=sector&estado=1', 'cuadrante_sector_id', 'id', 'nombre_sector', 'Sector', row.sector_id);
            cargarSelectCascada('index.php?url=ficha/obtenerCatalogo&cat=organismo&estado=1', 'cuadrante_organismo_id', 'id', 'nombre_organismo', 'Organismo', row.organismo_id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCuadrante')).show();

        } else if (catalogo === 'organismo') {
            $('#cat_simple_catalogo').val('organismo');
            $('#cat_simple_accion').val('editar');
            $('#cat_simple_id').val(row.id);
            $('#cat_simple_label').text('Nombre del Organismo');
            $('#cat_simple_valor').val(row.nombre_organismo);
            $('#cat_simple_descripcion').val(row.descripcion || '');
            $('#modalCatalogoSimpleTitulo').text(`Editar Organismo #${row.id}`);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCatalogoSimple')).show();

        } else if (catalogo === 'motivo_cierre') {
            $('#cat_simple_catalogo').val('motivo_cierre');
            $('#cat_simple_accion').val('editar');
            $('#cat_simple_id').val(row.id);
            // Preservar el contexto original del registro al editar
            $('#cat_simple_contexto').val(row.contexto || 'ficha');
            $('#cat_simple_label').text('Nombre del Motivo');
            $('#cat_simple_valor').val(row.nombre);
            $('#cat_simple_descripcion').val(row.descripcion || '');
            const titulo = row.contexto === 'organismo'
                ? `Editar Motivo de Cancelación #${row.id}`
                : `Editar Motivo de Cierre #${row.id}`;
            $('#modalCatalogoSimpleTitulo').text(titulo);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCatalogoSimple')).show();
        }
    });

    // Control unificado para el cambio de estado (Toggle Activo/Inactivo)
    $(document).on('click', '.btn-toggle-cat-estado', function () {
        const id       = $(this).data('id');
        const catalogo = $(this).data('cat');
        const estado   = $(this).data('estado');
        const activando = (estado == 0);

        const tablas = { 
            tipo_emergencia: [dtTipos, dtTiposInactivos], 
            caso: [dtCasos, dtCasosInactivos], 
            municipio: [dtMunicipios, dtMunicipiosInactivos],  
            parroquia: [dtParroquias, dtParroquiasInactivos],
            comuna: typeof dtComunas !== 'undefined' ? [dtComunas] : [],
            sector: typeof dtSectores !== 'undefined' ? [dtSectores] : [],
            cuadrante: typeof dtCuadrantes !== 'undefined' ? [dtCuadrantes] : [],
            organismo: [dtOrganismos, dtOrganismosInactivos],
            motivo_cierre: [
                dtMotivos, dtMotivosInactivos,
                ...(typeof dtMotivosOrg !== 'undefined' && dtMotivosOrg ? [dtMotivosOrg] : []),
                ...(typeof dtMotivosOrgInactivos !== 'undefined' && dtMotivosOrgInactivos ? [dtMotivosOrgInactivos] : []),
            ],
        };
        confirmarEstado(id, catalogo, tablas[catalogo], activando);
    });

    // Limpieza de delegaciones legacy para evitar duplicidad de disparos
    $(document).off('click', '.btn-eliminar-cat');
    $(document).off('click', '.btn-restaurar-cat');

});
