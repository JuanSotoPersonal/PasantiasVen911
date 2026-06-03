/**
 * reportes.js - Gestión de filtrado dinámico, configuración de períodos mediante selects,
 * y descarga unificada de reportes en pantalla, PDF, CSV y Excel XLSX.
 */

document.addEventListener('DOMContentLoaded', function () {

    const formFiltros          = document.getElementById('formFiltrosReporte');
    const tbody                = document.getElementById('tbodyReportes');
    const badgeTotal           = document.getElementById('totalResultadosBadge');
    const selectReportType     = document.getElementById('report_type');
    const selectPresetDate     = document.getElementById('date_range_preset');
    const btnFiltrar           = document.getElementById('btnFiltrar');
    const btnLimpiar           = document.getElementById('btnLimpiarFiltros');
    const COLSPAN              = 8;

    // Inputs ocultos de fechas
    const inputRealDesde       = document.getElementById('filtro_desde');
    const inputRealHasta       = document.getElementById('filtro_hasta');

    // Secciones dinámicas
    const sectionCustomDates   = document.getElementById('section_custom_dates');
    const sectionMonthSelect   = document.getElementById('section_month_select');
    const sectionPresets       = document.getElementById('section_date_presets');
    const sectionAdvanced      = document.getElementById('section_advanced_filters');

    // Inputs personalizados
    const inputCustomDesde     = document.getElementById('input_custom_desde');
    const inputCustomHasta     = document.getElementById('input_custom_hasta');

    // Selectores acumulados
    const selectAcumuladoMes   = document.getElementById('select_acumulado_mes');
    const selectAcumuladoAnio  = document.getElementById('select_acumulado_anio');

    // -----------------------------------------------------------------------
    // 1. INICIALIZAR SELECT2
    // -----------------------------------------------------------------------
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // -----------------------------------------------------------------------
    // 2. CASCADA: Tipo de Emergencia → Tipo de Caso
    // -----------------------------------------------------------------------
    const selectEmergencia = document.getElementById('filtro_emergencia');
    const selectCaso       = document.getElementById('filtro_caso');

    const todasOpciones = Array.from(selectCaso.options).map(opt => ({
        value:   opt.value,
        text:    opt.text,
        tipoId:  opt.dataset.tipo || ''
    }));

    selectEmergencia.addEventListener('change', function () {
        const tipoSeleccionado = this.value;

        if ($.fn.select2) $(selectCaso).select2('destroy');

        selectCaso.innerHTML = '<option value="">Todos los casos</option>';

        todasOpciones
            .filter(o => o.value === '' || !tipoSeleccionado || o.tipoId === tipoSeleccionado)
            .forEach(o => {
                if (o.value === '') return;
                const opt = document.createElement('option');
                opt.value         = o.value;
                opt.textContent   = o.text;
                opt.dataset.tipo  = o.tipoId;
                selectCaso.appendChild(opt);
            });

        if ($.fn.select2) $(selectCaso).select2({ theme: 'bootstrap-5', width: '100%' });
    });

    // -----------------------------------------------------------------------
    // 3. CÁLCULO DINÁMICO DE FECHAS PRESET (Sincronizado con inputs ocultos)
    // -----------------------------------------------------------------------
    function formatFecha(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function actualizarFechasOcultas() {
        const preset = selectPresetDate.value;
        const hoy = new Date();

        if (preset === 'today') {
            const strHoy = formatFecha(hoy);
            inputRealDesde.value = strHoy;
            inputRealHasta.value = strHoy;
        } else if (preset === 'yesterday') {
            const ayer = new Date();
            ayer.setDate(hoy.getDate() - 1);
            const strAyer = formatFecha(ayer);
            inputRealDesde.value = strAyer;
            inputRealHasta.value = strAyer;
        } else if (preset === 'this_week') {
            // Lunes de la semana actual
            const diaSemana = hoy.getDay();
            const dif = hoy.getDate() - diaSemana + (diaSemana === 0 ? -6 : 1);
            const lunes = new Date(hoy);
            lunes.setDate(dif);
            inputRealDesde.value = formatFecha(lunes);
            inputRealHasta.value = formatFecha(hoy);
        } else if (preset === 'this_month') {
            const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            inputRealDesde.value = formatFecha(primerDia);
            inputRealHasta.value = formatFecha(hoy);
        } else if (preset === 'custom') {
            inputRealDesde.value = inputCustomDesde.value;
            inputRealHasta.value = inputCustomHasta.value;
        }
    }

    selectPresetDate.addEventListener('change', function () {
        if (this.value === 'custom') {
            sectionCustomDates.classList.remove('d-none');
        } else {
            sectionCustomDates.classList.add('d-none');
        }
        actualizarFechasOcultas();
    });

    inputCustomDesde.addEventListener('change', actualizarFechasOcultas);
    inputCustomHasta.addEventListener('change', actualizarFechasOcultas);

    // Inicializar fechas al cargar la página
    actualizarFechasOcultas();

    // -----------------------------------------------------------------------
    // 4. CAMBIO DE TIPO DE REPORTE (Ajustar controles y botón principal)
    // -----------------------------------------------------------------------
    selectReportType.addEventListener('change', function () {
        const tipo = this.value;

        if (tipo === 'xlsx') {
            // Reporte Acumulado Mensual Excel
            sectionMonthSelect.classList.remove('d-none');
            sectionPresets.classList.add('d-none');
            sectionCustomDates.classList.add('d-none');
            sectionAdvanced.classList.add('d-none'); // Es condicional a la matriz fija, no aplican filtros individuales

            btnFiltrar.className = 'btn btn-success fw-semibold';
            btnFiltrar.innerHTML = '<i class="bi bi-download me-1"></i> Descargar Acumulado (XLSX)';
        } else {
            // Reportes filtrados
            sectionMonthSelect.classList.add('d-none');
            sectionPresets.classList.remove('d-none');
            if (selectPresetDate.value === 'custom') {
                sectionCustomDates.classList.remove('d-none');
            }
            sectionAdvanced.classList.remove('d-none');

            if (tipo === 'preview') {
                btnFiltrar.className = 'btn btn-success fw-semibold';
                btnFiltrar.innerHTML = '<i class="bi bi-search me-1"></i> Generar Búsqueda';
            } else if (tipo === 'pdf') {
                btnFiltrar.className = 'btn btn-danger fw-semibold';
                btnFiltrar.innerHTML = '<i class="bi bi-file-pdf-fill me-1"></i> Exportar PDF';
            } else if (tipo === 'xlsx_det') {
                btnFiltrar.className = 'btn btn-success fw-semibold';
                btnFiltrar.innerHTML = '<i class="bi bi-file-excel-fill me-1"></i> Exportar Excel (XLSX)';
            }
        }
    });

    // Trigger inicial de estilos del tipo de reporte
    selectReportType.dispatchEvent(new Event('change'));

    // -----------------------------------------------------------------------
    // 5. PROCESAMIENTO Y ENVÍO DE FORMULARIO (AJAX o Descarga Directa)
    // -----------------------------------------------------------------------
    formFiltros.addEventListener('submit', function (e) {
        e.preventDefault();
        
        const tipo = selectReportType.value;

        if (tipo === 'preview') {
            ejecutarBusquedaEnPantalla();
        } else if (tipo === 'pdf') {
            dispararExportacionSincrona('pdf');
        } else if (tipo === 'xlsx_det') {
            dispararExportacionSincrona('xlsx_det');
        } else if (tipo === 'xlsx') {
            ejecutarDescargaAcumuladoExcel();
        }
    });

    // Acción A: Búsqueda AJAX
    function ejecutarBusquedaEnPantalla() {
        const formData = new FormData(formFiltros);
        
        btnFiltrar.disabled  = true;
        const htmlOrig = btnFiltrar.innerHTML;
        btnFiltrar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Buscando...';
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
                btnFiltrar.disabled  = false;
                btnFiltrar.innerHTML = htmlOrig;
            });
    }

    // Acción B: Descargas síncronas filtradas (PDF / CSV)
    function dispararExportacionSincrona(formato) {
        const htmlOrig = btnFiltrar.innerHTML;
        
        btnFiltrar.disabled = true;
        btnFiltrar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generando...';

        // Asegurar que las fechas ocultas estén actualizadas
        actualizarFechasOcultas();

        // Agregar input de formato temporal
        const inputFormato = document.createElement('input');
        inputFormato.type = 'hidden';
        inputFormato.name = 'formato';
        inputFormato.value = formato;
        formFiltros.appendChild(inputFormato);
        
        const actionOrig = formFiltros.action;
        const targetOrig = formFiltros.target;
        
        formFiltros.action = 'index.php?url=reporte/exportarSincrono';
        formFiltros.target = '_blank';
        formFiltros.submit();
        
        setTimeout(() => {
            formFiltros.action = actionOrig || '';
            formFiltros.target = targetOrig || '';
            formFiltros.removeChild(inputFormato);
            btnFiltrar.disabled = false;
            btnFiltrar.innerHTML = htmlOrig;
        }, 2000);
    }

    // Acción C: Descarga del reporte Acumulado de Incidencias Excel XLSX
    function ejecutarDescargaAcumuladoExcel() {
        const mes = selectAcumuladoMes.value;
        const anio = selectAcumuladoAnio.value;

        if (!mes || !anio) {
            Swal.fire('Atención', 'Selección de mes y año inválida.', 'warning');
            return;
        }

        const htmlOrig = btnFiltrar.innerHTML;
        btnFiltrar.disabled = true;
        btnFiltrar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generando XLSX...';

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?url=reporte/exportarAcumuladoMensualExcel';
        form.target = '_blank';

        const inputMes = document.createElement('input');
        inputMes.type = 'hidden';
        inputMes.name = 'mes';
        inputMes.value = mes;
        form.appendChild(inputMes);

        const inputAnio = document.createElement('input');
        inputAnio.type = 'hidden';
        inputAnio.name = 'anio';
        inputAnio.value = anio;
        form.appendChild(inputAnio);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(() => {
            btnFiltrar.disabled = false;
            btnFiltrar.innerHTML = htmlOrig;
        }, 3000);
    }

    // -----------------------------------------------------------------------
    // 6. RENDERIZAR TABLA E KPIS
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
            'Cancelada':  'bg-danger-subtle text-danger'
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
                        <button class="btn btn-sm btn-light btn-ver-detalle" data-id="${f.id}" title="Ver ficha">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;
    }

    function actualizarKPIs(resumen) {
        document.getElementById('kpi_total').textContent       = resumen.total;
        document.getElementById('kpi_atendidas').textContent   = resumen.atendidas;
        document.getElementById('kpi_pendientes').textContent  = resumen.pendientes;
        document.getElementById('kpi_proceso').textContent     = resumen.en_proceso;
        document.getElementById('kpi_cerradas').textContent    = resumen.canceladas;
        document.getElementById('kpi_efectividad').textContent = resumen.efectividad + '%';
    }

    // -----------------------------------------------------------------------
    // 7. LIMPIAR FILTROS
    // -----------------------------------------------------------------------
    btnLimpiar.addEventListener('click', function () {
        formFiltros.reset();
        
        // Limpieza de Select2
        if ($.fn.select2) {
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

        // Restablecer selectores de tipo y fecha
        selectReportType.value = 'preview';
        selectReportType.dispatchEvent(new Event('change'));

        selectPresetDate.value = 'today';
        selectPresetDate.dispatchEvent(new Event('change'));

        tbody.innerHTML = `<tr><td colspan="${COLSPAN}" class="text-center py-5 text-muted"><i class="bi bi-search display-4 d-block mb-2"></i>Utilice los filtros para iniciar la búsqueda</td></tr>`;
        actualizarKPIs({ total: 0, atendidas: 0, pendientes: 0, en_proceso: 0, cerradas: 0, efectividad: 0 });
        badgeTotal.textContent = '0 registros';
    });

    // -----------------------------------------------------------------------
    // 8. ENLAZAR BOTONES DEL HEADER (Si el usuario hace click arriba, cambia select y busca)
    // -----------------------------------------------------------------------
    const headerPDF = document.getElementById('btnExportarPDF');
    if (headerPDF) {
        headerPDF.addEventListener('click', function () {
            selectReportType.value = 'pdf';
            selectReportType.dispatchEvent(new Event('change'));
            formFiltros.dispatchEvent(new Event('submit'));
        });
    }

    const headerExcel = document.getElementById('btnExportarExcel');
    if (headerExcel) {
        headerExcel.addEventListener('click', function () {
            selectReportType.value = 'xlsx_det';
            selectReportType.dispatchEvent(new Event('change'));
            formFiltros.dispatchEvent(new Event('submit'));
        });
    }
});
