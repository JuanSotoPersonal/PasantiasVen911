/**
 * home_graficas.js - Módulo de Visualización Estadística (Dashboard)
 * 
 * Gestiona el renderizado de gráficos analíticos utilizando la librería ApexCharts.
 * Consume datos inyectados globalmente por el controlador para mostrar la
 * distribución de usuarios y estados del sistema de forma interactiva.
 */

document.addEventListener('DOMContentLoaded', function() {

    // 1. EXTRACCIÓN Y VALIDACIÓN DE DATOS (INJECTED DATA)
    // Se verifica la existencia del objeto de estadísticas definido en la vista
    if (!window.VENT911_STATS) {
      console.warn('[HomeCharts] Advertencia: No se encontraron datos de estadísticas vinculados.');
      return;
    }

    const stats = window.VENT911_STATS;
    
    // 2. CONFIGURACIÓN DE APEXCHARTS (GRÁFICO DE DONA)
    const options = {
      // Datos de las series (Activos vs Inactivos)
      series: [stats.activo, stats.inactivo],
      
      chart: {
        type: 'donut',
        height: 350,
        fontFamily: 'inherit' // Hereda la tipografía del sistema (Inter/Outfit)
      },
      
      labels: ['Activos', 'Inactivos'],
      
      // Paleta institucional: Verde corporativo y Rojo alerta
      colors: ['#16a34a', '#dc2626'], 
      
      legend: {
        position: 'bottom'
      },
      
      stroke: {
        show: false // Estilo sin bordes para estética Glassmorphism/Flat
      },
      
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total Usuarios',
                color: '#064e3b', // Verde oscuro profundo
                formatter: function (w) {
                  // Cálculo acumulado del total de la serie
                  return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                }
              }
            }
          }
        }
      },
      
      // Optimización para dispositivos móviles
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 280
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    };

    // 3. INSTANCIACIÓN Y RENDERIZADO
    const chartContainer = document.querySelector("#usuariosChart");
    
    if (chartContainer) {
        const chart = new ApexCharts(chartContainer, options);
        chart.render();
    }
});
