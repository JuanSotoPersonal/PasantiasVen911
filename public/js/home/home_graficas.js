/**
 * Módulo de Gráficas del Dashboard (Home)
 * 
 * Este archivo centraliza la lógica de renderizado de estadísticas
 * utilizando ApexCharts, separando la lógica de presentación de la vista.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si los datos están disponibles globalmente
    if (!window.VENT911_STATS) {
      console.warn('[HomeCharts] No se encontraron datos de estadísticas.');
      return;
    }

    const stats = window.VENT911_STATS;
    
    const options = {
      series: [stats.activo, stats.inactivo],
      chart: {
        type: 'donut',
        height: 350,
        fontFamily: 'inherit'
      },
      labels: ['Activos', 'Inactivos'],
      colors: ['#16a34a', '#dc2626'], // Verde corporativo y Rojo alerta
      legend: {
        position: 'bottom'
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total Usuarios',
                color: '#064e3b',
                formatter: function (w) {
                  return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                }
              }
            }
          }
        }
      },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    };

    const chart = new ApexCharts(document.querySelector("#usuariosChart"), options);
    chart.render();
});
