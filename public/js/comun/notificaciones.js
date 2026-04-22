/**
 * Módulo: Notificaciones en Tiempo Real (SSE)
 * Motor: Server-Sent Events (EventSource nativo del navegador)
 * Archivo: public/js/notificaciones.js
 */
(function () {
    'use strict';

    // ---- Estado interno ----
    let fuenteSSE = null;
    const INTERVALO_RECONEXION_MS = 8000;
    const iconosPorTipo = {
        alerta:         { clase: 'tipo-alerta',  icono: 'bi-exclamation-triangle-fill' },
        info:           { clase: 'tipo-info',    icono: 'bi-info-circle-fill'          },
        cambio_estado:  { clase: 'tipo-cambio',  icono: 'bi-arrow-repeat'              },
        default:        { clase: 'tipo-info',    icono: 'bi-bell-fill'                 },
    };

    // ---- DOM ----
    const $badge    = document.getElementById('notif-badge');
    const $lista    = document.getElementById('notif-lista');
    const $vacio    = document.getElementById('notif-vacio');
    const $btnTodas = document.getElementById('btn-marcar-todas');

    if (!$badge) return; // Salir si la navbar no tiene el panel

    // ================================================================
    // CONEXIÓN SSE
    // ================================================================
    function conectarSSE() {
        if (fuenteSSE) fuenteSSE.close();

        fuenteSSE = new EventSource('index.php?url=notificacion/stream');

        fuenteSSE.onmessage = function (evento) {
            try {
                const datos = JSON.parse(evento.data);
                renderizarNotificaciones(datos);
            } catch (e) {
                console.warn('[Notificaciones] Error al parsear datos SSE:', e);
            }
        };

        fuenteSSE.onerror = function () {
            // El navegador reconecta automáticamente con EventSource.
            // Si la conexión cierra permanentemente (readyState=2), reconectamos manual.
            if (fuenteSSE.readyState === EventSource.CLOSED) {
                setTimeout(conectarSSE, INTERVALO_RECONEXION_MS);
            }
        };
    }

    // ================================================================
    // RENDERIZADO
    // ================================================================
    function renderizarNotificaciones(notificaciones) {
        if (!Array.isArray(notificaciones)) return;

        const cantidad = notificaciones.length;

        // Badge
        if (cantidad > 0) {
            $badge.textContent = cantidad > 99 ? '99+' : cantidad;
            $badge.classList.remove('d-none');
        } else {
            $badge.classList.add('d-none');
        }

        // Limpiar lista (menos el ítem vacío)
        const items = $lista.querySelectorAll('.notif-item');
        items.forEach(item => item.remove());

        if (cantidad === 0) {
            $vacio.style.display = 'block';
            return;
        }

        $vacio.style.display = 'none';

        notificaciones.forEach(notif => {
            const li = document.createElement('li');
            li.className = 'notif-item' + (notif.leido == 0 ? ' no-leido' : ' leido');
            li.dataset.id = notif.id;

            const config = iconosPorTipo[notif.tipo] || iconosPorTipo.default;
            const tiempo = formatearTiempo(notif.fecha_creacion);

            li.innerHTML = `
                <div class="notif-icono ${config.clase}">
                    <i class="bi ${config.icono}"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="notif-mensaje mb-0">${window.escapeHTML(notif.mensaje)}</p>
                    <span class="notif-tiempo"><i class="bi bi-clock me-1"></i>${tiempo}</span>
                </div>
            `;

            li.addEventListener('click', () => marcarLeida(notif.id, li));
            $lista.insertBefore(li, $vacio);
        });
    }

    // ================================================================
    // ACCIONES
    // ================================================================
    function marcarLeida(idNotif, elemento) {
        fetch(`index.php?url=notificacion/marcarLeida`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(idNotif)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                elemento.classList.remove('no-leido');
                elemento.classList.add('leido');
                actualizarBadgeManual(-1);
            }
        })
        .catch(() => {}); // Silencioso
    }

    if ($btnTodas) {
        $btnTodas.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            fetch(`index.php?url=notificacion/marcarTodas`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $lista.querySelectorAll('.notif-item').forEach(item => {
                        item.classList.remove('no-leido');
                        item.classList.add('leido');
                    });
                    $badge.classList.add('d-none');
                }
            })
            .catch(() => {});
        });
    }

    // ================================================================
    // HELPERS
    // ================================================================
    function actualizarBadgeManual(delta) {
        const actual = parseInt($badge.textContent) || 0;
        const nuevo = Math.max(0, actual + delta);
        if (nuevo === 0) {
            $badge.classList.add('d-none');
        } else {
            $badge.textContent = nuevo;
            $badge.classList.remove('d-none');
        }
    }

    function formatearTiempo(fechaStr) {
        if (!fechaStr) return '';
        const diff = Math.floor((Date.now() - new Date(fechaStr).getTime()) / 1000);
        if (diff < 60)    return 'Hace un momento';
        if (diff < 3600)  return `Hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
        return `Hace ${Math.floor(diff / 86400)} días`;
    }

    // ================================================================
    // ARRANCAR
    // ================================================================
    conectarSSE();

})();
