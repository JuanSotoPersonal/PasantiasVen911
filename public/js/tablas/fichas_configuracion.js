// fichas_configuracion.js
// Gestión ABMC de configuración del sistema: Tipos de Emergencia, Casos, Municipios, Parroquias

$(function () {

    const lang = window.Ven911DataTablesLang;

    // ================================================================
    // Función centralizada de guardado de registros de configuración
    // ================================================================
    function guardarCatalogo(datos, tablaRef, modalId, callback) {
        $.post('index.php?url=ficha/guardarCatalogo', datos, function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById(modalId))?.hide();
                Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                tablaRef.ajax.reload(null, false);
                if (callback) callback();
            } else {
                Swal.fire('Error', res.message || 'No se pudo guardar.', 'error');
            }
        }, 'json').fail(() => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'));
    }

    function confirmarEliminar(id, catalogo, tablaRef, callback) {
        Swal.fire({
            title: '¿Eliminar registro?',
            text:  'Esta acción no se puede deshacer.',
            icon:  'warning',
            showCancelButton:  true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText:  'Cancelar',
        }).then(result => {
            if (!result.isConfirmed) return;
            guardarCatalogo({ catalogo, accion: 'eliminar', id }, tablaRef, null, callback);
        });
    }

    function btnsAccion(row, catalogo) {
        return `
            <button class="btn btn-ven-edit btn-accion btn-editar-cat me-1"
                    data-cat="${catalogo}" data-row='${JSON.stringify(row)}' title="Editar">
                <i class="bi bi-pencil-fill"></i>
            </button>
            <button class="btn btn-danger btn-accion btn-eliminar-cat"
                    data-cat="${catalogo}" data-id="${row.id}" title="Eliminar">
                <i class="bi bi-trash-fill"></i>
            </button>`;
    }

    // ================================================================
    // TIPOS DE EMERGENCIA
    // ================================================================
    const dtTipos = $('#tablaTipos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=tipo_emergencia', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre', render: (d) => escapeHTML(d) },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'tipo_emergencia') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    $('#btnNuevoTipo').on('click', () => {
        $('#cat_simple_catalogo').val('tipo_emergencia');
        $('#cat_simple_accion').val('crear');
        $('#cat_simple_id').val('0');
        $('#cat_simple_label').text('Nombre del Tipo');
        $('#cat_simple_valor').val('');
        // El campo se mapea a "nombre" para tipo y municipio
        $('#formCatalogoSimple input[name="nombre"]').attr('name', 'nombre');
        $('#modalCatalogoSimpleTitulo').text('Nuevo Tipo de Emergencia');
        new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
    });

    $('#btnGuardarCatSimple').on('click', () => {
        const datos = Object.fromEntries(new FormData(document.getElementById('formCatalogoSimple')));
        const catalogo = datos.catalogo;
        // Ajustar el campo correcto por catálogo
        if (catalogo === 'municipio') datos.nombre_municipio = datos.nombre;
        guardarCatalogo(datos, catalogo === 'tipo_emergencia' ? dtTipos : dtMunicipios, 'modalCatalogoSimple');
    });

    // ================================================================
    // CASOS
    // ================================================================
    const dtCasos = $('#tablaCasos').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=caso', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_caso', render: (d) => escapeHTML(d) },
            { data: 'tipo_emergencia', render: (d) => `<span class="badge bg-secondary">${escapeHTML(d)}</span>` },
            { data: 'descripcion', render: (d) => d ? `<small class="text-muted">${escapeHTML(d)}</small>` : '<em class="text-muted">—</em>' },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'caso') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

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
        guardarCatalogo(datos, dtCasos, 'modalCaso');
    });

    // ================================================================
    // MUNICIPIOS
    // ================================================================
    const dtMunicipios = $('#tablaMunicipios').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=municipio', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'municipio') },
        ],
        language: lang, pageLength: 10, order: [[1, 'asc']],
    });

    $('#btnNuevoMunicipio').on('click', () => {
        $('#cat_simple_catalogo').val('municipio');
        $('#cat_simple_accion').val('crear');
        $('#cat_simple_id').val('0');
        $('#cat_simple_label').text('Nombre del Municipio');
        $('#cat_simple_valor').val('').attr('name', 'nombre_municipio');
        $('#modalCatalogoSimpleTitulo').text('Nuevo Municipio');
        new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();
    });

    // ================================================================
    // PARROQUIAS
    // ================================================================
    const dtParroquias = $('#tablaParroquias').DataTable({
        ajax: { url: 'index.php?url=ficha/obtenerCatalogo&cat=parroquia', dataSrc: 'data' },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false, width: '50px' },
            { data: 'nombre_parroquia', render: (d) => escapeHTML(d) },
            { data: 'nombre_municipio', render: (d) => escapeHTML(d) },
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: (d, t, row) => btnsAccion(row, 'parroquia') },
        ],
        language: lang, pageLength: 10, order: [[2, 'asc'], [1, 'asc']],
    });

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

    $('#btnGuardarParroquia').on('click', () => {
        const datos = Object.fromEntries(new FormData(document.getElementById('formParroquia')));
        guardarCatalogo(datos, dtParroquias, 'modalParroquia');
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
            $('#cat_simple_valor').val(row.nombre).attr('name', 'nombre');
            $('#modalCatalogoSimpleTitulo').text(`Editar Tipo de Emergencia #${row.id}`);
            new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();

        } else if (catalogo === 'caso') {
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
            $('#cat_simple_valor').val(row.nombre_municipio).attr('name', 'nombre_municipio');
            $('#modalCatalogoSimpleTitulo').text(`Editar Municipio #${row.id}`);
            new bootstrap.Modal(document.getElementById('modalCatalogoSimple')).show();

        } else if (catalogo === 'parroquia') {
            $('#parroquia_accion').val('editar');
            $('#parroquia_id').val(row.id);
            $('#parroquia_municipio_id').val(row.municipio_id);
            $('#parroquia_nombre').val(row.nombre_parroquia);
            $('#modalParroquiaTitulo').text(`Editar Parroquia #${row.id}`);
            new bootstrap.Modal(document.getElementById('modalParroquia')).show();
        }
    });

    // ================================================================
    // ELIMINAR: delegación unificada
    // ================================================================
    $(document).on('click', '.btn-eliminar-cat', function () {
        const catalogo = $(this).data('cat');
        const id       = $(this).data('id');
        const tablas   = { tipo_emergencia: dtTipos, caso: dtCasos, municipio: dtMunicipios, parroquia: dtParroquias };
        confirmarEliminar(id, catalogo, tablas[catalogo]);
    });

});
