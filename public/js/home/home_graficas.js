/**
 * home_graficas.js - Módulo de Visualización Estadística (Dashboard Multi-Rol)
 * 
 * Gestiona el renderizado de gráficos analíticos según el rol del usuario.
 * Utiliza ApexCharts para visualizaciones dinámicas y responsivas.
 */

document.addEventListener('DOMContentLoaded', function() {

    // 1. EXTRACCIÓN DE DATOS Y ROL
    if (!window.VENT911_STATS || !window.USER_ROL) return;

    const stats = window.VENT911_STATS;
    const rol = window.USER_ROL;
    const chartInstances = {}; // Almacén para actualizar gráficas sin recrearlas

    // Colores Institucionales
    const colors = {
        primary: '#2563eb', success: '#16a34a', danger: '#dc2626', warning: '#ca8a04', info: '#0891b2', secondary: '#64748b'
    };

    const getBaseOptions = (type, height = 300) => ({
        chart: { type, height, fontFamily: 'inherit', toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        colors: [colors.primary, colors.success, colors.danger, colors.warning, colors.info]
    });

    // 2. INICIALIZACIÓN POR ROL
    switch (rol) {
        case 1: renderAdminStats(stats); break;
        case 2: renderOperadorStats(stats); break;
        case 3: renderDespachadorStats(stats); break;
        case 4: renderJefaturaStats(stats); break;
    }

    /**
     * Función Global para actualizar el Dashboard vía AJAX
     */
    window.actualizarDashboard = function() {
        fetch('index.php?url=home/obtenerStatsAjax')
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    actualizarGraficasSegunRol(res.datos);
                }
            })
            .catch(err => console.error('Error actualizando dashboard:', err));
    };

    // Actualización automática cada 2 minutos
    setInterval(window.actualizarDashboard, 120000);

    function actualizarGraficasSegunRol(s) {
        // Actualizar Contadores Críticos (Widgets)
        const updateText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        if (rol === 1) {
            if (chartInstances.roles) chartInstances.roles.updateSeries(s.roles.map(r => parseInt(r.total)));
            if (chartInstances.emergencias) {
                chartInstances.emergencias.updateSeries([{ data: s.emergencias.map(e => parseInt(e.total)) }]);
                chartInstances.emergencias.updateOptions({ xaxis: { categories: s.emergencias.map(e => e.nombre) } });
            }
            if (chartInstances.estados) chartInstances.estados.updateSeries([{ data: s.estados.map(e => parseInt(e.total)) }]);
        } else if (rol === 2) {
            updateText('counter_total_hoy', s.total_hoy);
            if (chartInstances.misEstados) chartInstances.misEstados.updateSeries(s.estados.map(e => parseInt(e.total)));
            if (chartInstances.semana) chartInstances.semana.updateSeries([{ data: s.semana.map(d => parseInt(d.total)) }]);
        } else if (rol === 3) {
            updateText('counter_pendientes_globales', s.pendientes_globales);
            updateText('counter_mis_despachos_activos', s.mis_despachos_activos);
            if (chartInstances.organismos) chartInstances.organismos.updateSeries([{ data: s.top_organismos.map(o => parseInt(o.total)) }]);
        } else if (rol === 4) {
            updateText('counter_total_hoy_jefatura', s.kpis.total_hoy);
            updateText('counter_efectividad', s.kpis.efectividad + '%');
            let pendTotal = 0;
            s.municipios.forEach(m => pendTotal += parseInt(m.pendientes));
            updateText('counter_pendientes_jefatura', pendTotal);

            if (chartInstances.comparativa) {
                const hoyData = Array(24).fill(0);
                const ayerData = Array(24).fill(0);
                s.comparativa.hoy.forEach(h => hoyData[h.hora] = parseInt(h.total));
                s.comparativa.ayer.forEach(a => ayerData[a.hora] = parseInt(a.total));
                chartInstances.comparativa.updateSeries([
                    { name: 'Hoy', data: hoyData },
                    { name: 'Ayer', data: ayerData }
                ]);
            }
            if (chartInstances.cierres) {
                chartInstances.cierres.updateSeries(s.cierres.map(c => parseInt(c.total)));
                chartInstances.cierres.updateOptions({ labels: s.cierres.map(c => c.motivo) });
            }
            if (chartInstances.municipiosEficiencia) {
                chartInstances.municipiosEficiencia.updateSeries([
                    { name: 'Resueltos', data: s.municipios.map(m => parseInt(m.resueltos)) },
                    { name: 'Pendientes', data: s.municipios.map(m => parseInt(m.pendientes)) }
                ]);
            }
        }
    }

    // --- RENDERIZADORES ---

    function renderAdminStats(s) {
        if (s.roles && document.querySelector("#rolesChart")) {
            chartInstances.roles = new ApexCharts(document.querySelector("#rolesChart"), {
                ...getBaseOptions('donut'),
                series: s.roles.map(r => parseInt(r.total)),
                labels: s.roles.map(r => r.nombre_rol),
                plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Personal' } } } } }
            });
            chartInstances.roles.render();
        }

        if (s.emergencias && document.querySelector("#emergenciasChart")) {
            chartInstances.emergencias = new ApexCharts(document.querySelector("#emergenciasChart"), {
                ...getBaseOptions('bar'),
                series: [{ name: 'Incidentes', data: s.emergencias.map(e => parseInt(e.total)) }],
                xaxis: { categories: s.emergencias.map(e => e.nombre) },
                plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                colors: [colors.danger]
            });
            chartInstances.emergencias.render();
        }

        if (s.estados && document.querySelector("#estadosGlobalChart")) {
            chartInstances.estados = new ApexCharts(document.querySelector("#estadosGlobalChart"), {
                ...getBaseOptions('bar', 350),
                series: [{ name: 'Fichas', data: s.estados.map(e => parseInt(e.total)) }],
                xaxis: { categories: s.estados.map(e => e.estado_ficha) },
                colors: [colors.success]
            });
            chartInstances.estados.render();
        }
    }

    function renderOperadorStats(s) {
        if (s.estados && document.querySelector("#misEstadosChart")) {
            chartInstances.misEstados = new ApexCharts(document.querySelector("#misEstadosChart"), {
                ...getBaseOptions('donut', 250),
                series: s.estados.map(e => parseInt(e.total)),
                labels: s.estados.map(e => e.estado_ficha)
            });
            chartInstances.misEstados.render();
        }

        if (s.semana && document.querySelector("#actividadSemanalChart")) {
            chartInstances.semana = new ApexCharts(document.querySelector("#actividadSemanalChart"), {
                ...getBaseOptions('area'),
                series: [{ name: 'Fichas creadas', data: s.semana.map(d => parseInt(d.total)) }],
                xaxis: { categories: s.semana.map(d => d.fecha), type: 'datetime' },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 } }
            });
            chartInstances.semana.render();
        }
    }

    function renderDespachadorStats(s) {
        if (s.top_organismos && document.querySelector("#organismosChart")) {
            chartInstances.organismos = new ApexCharts(document.querySelector("#organismosChart"), {
                ...getBaseOptions('bar'),
                series: [{ name: 'Solicitudes', data: s.top_organismos.map(o => parseInt(o.total)) }],
                xaxis: { categories: s.top_organismos.map(o => o.nombre_organismo) },
                colors: [colors.info]
            });
            chartInstances.organismos.render();
        }
    }

    function renderJefaturaStats(s) {
        // Comparativa Temporal
        if (document.querySelector("#comparativaTemporalChart")) {
            const hoyData = Array(24).fill(0);
            const ayerData = Array(24).fill(0);
            s.comparativa.hoy.forEach(h => hoyData[h.hora] = parseInt(h.total));
            s.comparativa.ayer.forEach(a => ayerData[a.hora] = parseInt(a.total));

            chartInstances.comparativa = new ApexCharts(document.querySelector("#comparativaTemporalChart"), {
                ...getBaseOptions('area', 350),
                series: [
                    { name: 'Hoy', data: hoyData },
                    { name: 'Ayer', data: ayerData }
                ],
                xaxis: { categories: Array.from({length: 24}, (_, i) => `${i}:00`) },
                colors: [colors.primary, colors.secondary],
                stroke: { width: [3, 2], dashArray: [0, 5] }
            });
            chartInstances.comparativa.render();
        }

        // Calidad de Cierres
        if (document.querySelector("#cierresCalidadChart")) {
            chartInstances.cierres = new ApexCharts(document.querySelector("#cierresCalidadChart"), {
                ...getBaseOptions('donut', 350),
                series: s.cierres.map(c => parseInt(c.total)),
                labels: s.cierres.map(c => c.motivo),
                legend: { position: 'bottom' }
            });
            chartInstances.cierres.render();
        }

        // Eficiencia por Municipio
        if (document.querySelector("#municipiosEficienciaChart")) {
            chartInstances.municipiosEficiencia = new ApexCharts(document.querySelector("#municipiosEficienciaChart"), {
                ...getBaseOptions('bar', 400),
                series: [
                    { name: 'Resueltos', data: s.municipios.map(m => parseInt(m.resueltos)) },
                    { name: 'Pendientes', data: s.municipios.map(m => parseInt(m.pendientes)) }
                ],
                xaxis: { categories: s.municipios.map(m => m.nombre_municipio) },
                plotOptions: { bar: { stacked: true, borderRadius: 6 } },
                colors: [colors.success, colors.warning]
            });
            chartInstances.municipiosEficiencia.render();
        }
    }
});
