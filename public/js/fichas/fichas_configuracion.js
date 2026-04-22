// fichas_configuracion.js
// Gestión ABMC de configuración del sistema: Tipos de Emergencia, Casos, Municipios, Parroquias

$(function () {

    const lang = window.Ven911DataTablesLang;

    // ================================================================
    // HELPERS Y FUNCIONES DE UTILIDAD
    // ================================================================
    
    function actualizarContadoresInactivos() {
        let total = 0;
        $('.count-inactivo').each(function() {
            total += parseInt($(this).text()) || 0;
        });
    }

    function setupContadorDT(dt, elementId) {
        dt.on('xhr.dt', function(e, settings, json) {
            const count = (json && json.data) ? json.data.length : 0;
            $(`#${elementId}`).text(count).addClass('count-inactivo');
            actualizarContadoresInactivos();
        });
    }

    // ================================================================
    // Función centralizada de guardado de registros de configuración
    // ================================================================
    function guardarCatalogo(datos, tablasRef, modalId, callback) {
        $.post('index.php?url=ficha/guardarCatalogo', datos, function (res) {
            if (res.success) {
                if (modalId) {
                    const m = document.getElementById(modalId);
                    if (m) bootstrap.Modal.getInstance(m)?.hide();
                }
                Swal.fire({ icon: 'success', title: '¡Operación Exitosa!', text: res.message, timer: 1500, showConfirmButton: false });
                
                // Recargar tablas (puede ser una o un array)
                if (Array.isArray(tablasRef)) {
                    tablasRef.forEach(t => t.ajax.reload(null, false));
                } else if (tablasRef) {
                    tablasRef.ajax.reload(null, false);
                }
                
                if (callback) callback();
            } else {
                Swal.fire('Error', res.message || 'No se pudo completar la operación.', 'error');
            }
        }, 'json').fail(() => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'));
    }

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

    function btnsAccion(row, catalogo) {
        const isActivo = (row.estado == 1);
        
        if (!isActivo) {
            // Botón de restaurar igual al ícono de Usuarios Inactivos
            return `
                <button class="btn btn-ven-primary btn-accion btn-toggle-cat-estado"
                        data-id="${row.id}" data-cat="${catalogo}" data-estado="0"
                        title="Reactivar registro">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>`;
        }

        return `
            <button class="btn btn-ven-edit btn-accion btn-editar-cat me-1"
                    data-cat="${catalogo}" data-row='${JSON.stringify(row)}' title="Editar">
                <i class="bi bi-pencil-fill"></i>
            </button>`;
    }

    // ================================================================
    // TIPOS DE EMERGENCIA
    // ================================================================
    const dtTipos = $('#tablaTipos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=tipo_emergencia&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'tipo_emergencia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'tipo_emergencia') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtTiposInactivos = $('#tablaTiposInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=tipo_emergencia&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'tipo_emergencia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'tipo_emergencia') },
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

    $('#btnGuardarCatSimple').on('click', () => {
        const datos = Object.fromEntries(new FormData(document.getElementById('formCatalogoSimple')));
        const catalogo = datos.catalogo;
        
        // Mapeo manual para el controlador (aseguramos que las llaves específicas existan)
        if (catalogo === 'municipio') datos.nombre_municipio = datos.nombre;
        if (catalogo === 'organismo') datos.nombre_organismo = datos.nombre;
        
        const tablas = { 
            tipo_emergencia: [dtTipos, dtTiposInactivos], 
            municipio: [dtMunicipios, dtMunicipiosInactivos], 
            organismo: [dtOrganismos, dtOrganismosInactivos] 
        };
        guardarCatalogo(datos, tablas[catalogo], 'modalCatalogoSimple');
    });

    // ================================================================
    // CASOS
    // ================================================================
    const dtCasos = $('#tablaCasos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=caso&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_caso', render: (d) => escapeHTML(d) },
            { data: 'tipo_emergencia', render: (d) => `<span class="badge bg-secondary">${escapeHTML(d)}</span>` },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'caso'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'caso') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtCasosInactivos = $('#tablaCasosInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=caso&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_caso', render: (d) => escapeHTML(d) },
            { data: 'tipo_emergencia', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'caso'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'caso') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtCasosInactivos, 'count-inactivos-casos');

    // Filtro por tipo en la tabla de casos
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

    $('#btnGuardarCaso').on('click', () => {
        const datos = Object.fromEntries(new FormData(document.getElementById('formCaso')));
        guardarCatalogo(datos, [dtCasos, dtCasosInactivos], 'modalCaso');
    });

    // ================================================================
    // MUNICIPIOS
    // ================================================================
    const dtMunicipios = $('#tablaMunicipios').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=municipio&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'municipio'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'municipio') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtMunicipiosInactivos = $('#tablaMunicipiosInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=municipio&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'municipio'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'municipio') },
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

    // ================================================================
    // PARROQUIAS
    // ================================================================
    const dtParroquias = $('#tablaParroquias').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=parroquia&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_parroquia', render: (d) => escapeHTML(d) },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'parroquia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'parroquia') },
        ],
        language: lang, pageLength: 10, order: [[2, 'asc'], [1, 'asc']],
    });

    const dtParroquiasInactivos = $('#tablaParroquiasInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=parroquia&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_parroquia', render: (d) => escapeHTML(d) },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'parroquia'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'parroquia') },
        ],
        language: lang, pageLength: 5, searching: false, lengthChange: false
    });
    setupContadorDT(dtParroquiasInactivos, 'count-inactivos-parroquias');

    // Filtro por municipio en parroquias
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

    // ================================================================
    // ORGANISMOS
    // ================================================================
    const dtOrganismos = $('#tablaOrganismos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=organismo&estado=1', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_organismo', render: (d) => escapeHTML(d) },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'organismo'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'organismo') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    const dtOrganismosInactivos = $('#tablaOrganismosInactivos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=organismo&estado=0', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '40px' },
            { data: 'nombre_organismo', render: (d) => escapeHTML(d) },
            { data: null, render: (d, t, r) => renderEstadoBadge(r, 'organismo'), orderable: false, searchable: false },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'organismo') },
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

    // ================================================================
    // EDITAR: delegación unificada para todos los catálogos
    // ================================================================
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
            new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();

        } else if (catalogo === 'caso') {
            // ... (caso ya tiene descripción)
            $('#caso_accion').val('editar');
            $('#caso_id').val(row.id);
            $('#caso_tipo_id').val(row.tipo_emergencia_id);
            $('#caso_nombre').val(row.nombre_caso);
            $('#caso_descripcion').val(row.descripcion || '');
            $('#modalCasoTitulo').text(`Editar Caso #${row.id}`);
            new bootstrap.Modal(document.getElementById('modalCaso')).show();

        } else if (catalogo === 'municipio') {
            $('#cat_simple_catalogo').val('municipio');
            $('#cat_simple_accion').val('editar');
            $('#cat_simple_id').val(row.id);
            $('#cat_simple_label').text('Nombre del Municipio');
            $('#cat_simple_valor').val(row.nombre_municipio);
            $('#cat_simple_descripcion').val(row.descripcion || '');
            $('#modalCatalogoSimpleTitulo').text(`Editar Municipio #${row.id}`);
            new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();

        } else if (catalogo === 'parroquia') {
            $('#parroquia_accion').val('editar');
            $('#parroquia_id').val(row.id);
            $('#parroquia_municipio_id').val(row.municipio_id);
            $('#parroquia_nombre').val(row.nombre_parroquia);
            $('#parroquia_descripcion').val(row.descripcion || '');
            $('#modalParroquiaTitulo').text(`Editar Parroquia #${row.id}`);
            new bootstrap.Modal(document.getElementById('modalParroquia')).show();

        } else if (catalogo === 'organismo') {
            $('#cat_simple_catalogo').val('organismo');
            $('#cat_simple_accion').val('editar');
            $('#cat_simple_id').val(row.id);
            $('#cat_simple_label').text('Nombre del Organismo');
            $('#cat_simple_valor').val(row.nombre_organismo);
            $('#cat_simple_descripcion').val(row.descripcion || '');
            $('#modalCatalogoSimpleTitulo').text(`Editar Organismo #${row.id}`);
            new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
        }
    });

    // ================================================================
    // ELIMINAR / RESTAURAR (TOGGLE) via Badge de Estado
    // ================================================================
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
            organismo: [dtOrganismos, dtOrganismosInactivos] 
        };
        confirmarEstado(id, catalogo, tablas[catalogo], activando);
    });

    // Eliminar delegaciones antiguas si existieran
    $(document).off('click', '.btn-eliminar-cat');
    $(document).off('click', '.btn-restaurar-cat');

});
