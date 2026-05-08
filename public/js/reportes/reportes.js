/**
 * reportes.js - Gestión de filtrado dinámico y exportación de reportes
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const formFiltros = document.getElementById('formFiltrosReporte');
    const tbody = document.getElementById('tbodyReportes');
    const badgeTotal = document.getElementById('totalResultadosBadge');
    
    // Inicializar Select2 si está disponible
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    /**
     * Procesar búsqueda al enviar el formulario
     */
    formFiltros.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = document.getElementById('btnFiltrar');
        
        // Estado de carga
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Buscando...';
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';

        fetch('index.php?url=reporte/buscar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                renderizarTabla(res.data);
                actualizarKPIs(res.resumen);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'No se pudo procesar la búsqueda', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Generar Búsqueda';
        });
    });

    /**
     * Renderizar los datos en la tabla
     */
    function renderizarTabla(data) {
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron resultados</td></tr>';
            badgeTotal.textContent = '0 registros';
            return;
        }

        badgeTotal.textContent = data.length + ' registros';
        let html = '';
        
        data.forEach(f => {
            const badgeClass = f.estado_ficha === 'Atendido' ? 'bg-success-subtle' : (f.estado_ficha === 'Pendiente' ? 'bg-warning-subtle' : 'bg-secondary-subtle');
            
            html += `
                <tr>
                    <td class="small">${window.escapeHTML(f.fecha_creacion)}</td>
                    <td class="fw-bold text-primary">${window.escapeHTML(f.codigo_ficha)}</td>
                    <td>${window.escapeHTML(f.nombre_municipio)}</td>
                    <td>${window.escapeHTML(f.nombre_emergencia)}</td>
                    <td class="small">${window.escapeHTML(f.nombre_operador)}</td>
                    <td><span class="badge ${badgeClass}">${window.escapeHTML(f.estado_ficha)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-light btn-view-ficha" data-id="${f.id}">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }

    /**
     * Actualizar tarjetas de resumen
     */
    function actualizarKPIs(resumen) {
        document.getElementById('kpi_total').textContent = resumen.total;
        document.getElementById('kpi_atendidas').textContent = resumen.atendidas;
        document.getElementById('kpi_pendientes').textContent = resumen.pendientes;
        document.getElementById('kpi_efectividad').textContent = resumen.efectividad + '%';
    }

    /**
     * Botón Limpiar Filtros
     */
    document.getElementById('btnLimpiarFiltros').addEventListener('click', function() {
        formFiltros.reset();
        if ($.fn.select2) $('.select2').val(null).trigger('change');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">Utilice los filtros para iniciar la búsqueda</td></tr>';
        actualizarKPIs({ total: 0, atendidas: 0, pendientes: 0, efectividad: 0 });
        badgeTotal.textContent = '0 registros';
    });

    // Eventos para Exportación (Stubs para el siguiente paso)
    document.getElementById('btnExportarPDF').addEventListener('click', () => {
        Swal.fire('Info', 'Funcionalidad de exportación PDF en desarrollo', 'info');
    });

    document.getElementById('btnExportarExcel').addEventListener('click', () => {
        Swal.fire('Info', 'Funcionalidad de exportación Excel en desarrollo', 'info');
    });
});
