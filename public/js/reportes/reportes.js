/**
 * reportes.js - Gestión de filtrado dinámico y exportación de reportes
 * Incluye: cascada Emergencia→Caso, renderizado de tabla con Tipo de Caso, KPIs y exportación asíncrona.
 */

document.addEventListener('DOMContentLoaded', function () {

    const formFiltros  = document.getElementById('formFiltrosReporte');
    const tbody        = document.getElementById('tbodyReportes');
    const badgeTotal   = document.getElementById('totalResultadosBadge');
    const COLSPAN      = 8; // Columnas totales de la tabla

    // -----------------------------------------------------------------------
    // 1. INICIALIZAR SELECT2
    // -----------------------------------------------------------------------
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // -----------------------------------------------------------------------
    // 2. CASCADA: Tipo de Emergencia → filtra opciones de Tipo de Caso
    // -----------------------------------------------------------------------
    const selectEmergencia = document.getElementById('filtro_emergencia');
    const selectCaso       = document.getElementById('filtro_caso');

    // Guardar todas las opciones de caso al cargar
    const todasOpciones = Array.from(selectCaso.options).map(opt => ({
        value:   opt.value,
        text:    opt.text,
        tipoId:  opt.dataset.tipo || ''
    }));

    selectEmergencia.addEventListener('change', function () {
        const tipoSeleccionado = this.value;

        // Destruir Select2 para manipular el DOM libremente
        if ($.fn.select2) $(selectCaso).select2('destroy');

        // Limpiar opciones del caso
        selectCaso.innerHTML = '<option value="">Todos los casos</option>';

        todasOpciones
            .filter(o => o.value === '' || !tipoSeleccionado || o.tipoId === tipoSeleccionado)
            .forEach(o => {
                if (o.value === '') return; // El placeholder ya fue añadido
                const opt = document.createElement('option');
                opt.value         = o.value;
                opt.textContent   = o.text;
                opt.dataset.tipo  = o.tipoId;
                selectCaso.appendChild(opt);
            });

        // Re-inicializar Select2
        if ($.fn.select2) $(selectCaso).select2({ theme: 'bootstrap-5', width: '100%' });
    });

    // -----------------------------------------------------------------------
    // 3. BÚSQUEDA FILTRADA (AJAX)
    // -----------------------------------------------------------------------
    formFiltros.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const btn      = document.getElementById('btnFiltrar');

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Buscando...';
        tbody.innerHTML = `<tr><td colspan="${COLSPAN}" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>`;

        fetch('index.php?url=reporte/buscar', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    renderizarTabla(res.data);
                    actualizarKPIs(res.resumen);
                } else {
                    Swal.fire('Error', res.message || 'Error al buscar', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'No se pudo procesar la búsqueda.', 'error');
            })
            .finally(() => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-search me-1"></i> Generar Búsqueda';
            });
    });

    // -----------------------------------------------------------------------
    // 4. RENDERIZAR TABLA DE RESULTADOS
    // -----------------------------------------------------------------------
    function renderizarTabla(data) {
        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${COLSPAN}" class="text-center py-5 text-muted">No se encontraron resultados con los filtros aplicados.</td></tr>`;
            badgeTotal.textContent = '0 registros';
            return;
        }

        badgeTotal.textContent = data.length + ' registros';

        const estadoBadge = {
            'Atendido':   'bg-success-subtle text-success',
            'Pendiente':  'bg-warning-subtle text-warning',
            'En Proceso': 'bg-info-subtle text-info',
            'Cerrado':    'bg-secondary-subtle text-secondary'
        };

        let html = '';
        data.forEach((f, index) => {
            const badge = estadoBadge[f.estado_ficha] || 'bg-light';
            html += `
                <tr>
                    <td class="small text-muted">${index + 1}</td>
                    <td class="small text-nowrap">${window.escapeHTML(f.fecha_creacion)}</td>
                    <td>${window.escapeHTML(f.nombre_municipio)}</td>
                    <td>${window.escapeHTML(f.nombre_emergencia)}</td>
                    <td class="small">${window.escapeHTML(f.nombre_caso || '—')}</td>
                    <td class="small">${window.escapeHTML(f.nombre_operador)}</td>
                    <td><span class="badge ${badge} rounded-pill">${window.escapeHTML(f.estado_ficha)}</span></td>
                    <td>
                        <a href="index.php?url=ficha/detalle/${f.id}" target="_blank"
                           class="btn btn-sm btn-light" title="Ver ficha">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;
    }

    // -----------------------------------------------------------------------
    // 5. ACTUALIZAR KPIS
    // -----------------------------------------------------------------------
    function actualizarKPIs(resumen) {
        document.getElementById('kpi_total').textContent       = resumen.total;
        document.getElementById('kpi_atendidas').textContent   = resumen.atendidas;
        document.getElementById('kpi_pendientes').textContent  = resumen.pendientes;
        document.getElementById('kpi_proceso').textContent     = resumen.en_proceso;
        document.getElementById('kpi_cerradas').textContent    = resumen.cerradas;
        document.getElementById('kpi_efectividad').textContent = resumen.efectividad + '%';
    }

    // -----------------------------------------------------------------------
    // 6. LIMPIAR FILTROS
    // -----------------------------------------------------------------------
    document.getElementById('btnLimpiarFiltros').addEventListener('click', function () {
        formFiltros.reset();
        if ($.fn.select2) {
            // Restaurar select2 al estado inicial (incluyendo todas las opciones de caso)
            if ($.fn.select2) $(selectCaso).select2('destroy');
            selectCaso.innerHTML = '<option value="">Todos los casos</option>';
            todasOpciones.filter(o => o.value !== '').forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value; opt.textContent = o.text; opt.dataset.tipo = o.tipoId;
                selectCaso.appendChild(opt);
            });
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
            $('.select2').val(null).trigger('change');
        }
        tbody.innerHTML = `<tr><td colspan="${COLSPAN}" class="text-center py-5 text-muted"><i class="bi bi-search display-4 d-block mb-2"></i>Utilice los filtros para iniciar la búsqueda</td></tr>`;
        actualizarKPIs({ total: 0, atendidas: 0, pendientes: 0, en_proceso: 0, cerradas: 0, efectividad: 0 });
        badgeTotal.textContent = '0 registros';
    });

    // -----------------------------------------------------------------------
    // 7. EXPORTACIÓN SÍNCRONA (Descarga directa desde Backend)
    // -----------------------------------------------------------------------
    function dispararExportacionSincrona(formato) {
        const btnId      = formato === 'csv' ? 'btnExportarExcel' : 'btnExportarPDF';
        const btn        = document.getElementById(btnId);
        const htmlOrig   = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generando...';

        // Añadir input temporal de formato para enviar al backend
        const inputFormato = document.createElement('input');
        inputFormato.type = 'hidden';
        inputFormato.name = 'formato';
        inputFormato.value = formato;
        formFiltros.appendChild(inputFormato);
        
        // Alterar form para enviar POST al endpoint sincrónico en nueva pestaña
        const actionOrig = formFiltros.action;
        const targetOrig = formFiltros.target;
        
        formFiltros.action = 'index.php?url=reporte/exportarSincrono';
        formFiltros.target = '_blank';
        formFiltros.submit();
        
        // Restaurar formulario y estado del botón tras un breve retraso
        setTimeout(() => {
            formFiltros.action = actionOrig || '';
            formFiltros.target = targetOrig || '';
            formFiltros.removeChild(inputFormato);
            btn.disabled = false;
            btn.innerHTML = htmlOrig;
        }, 1500);
    }
    document.getElementById('btnExportarPDF').addEventListener('click', () => dispararExportacionSincrona('pdf'));
    document.getElementById('btnExportarExcel').addEventListener('click', () => dispararExportacionSincrona('csv'));
});
