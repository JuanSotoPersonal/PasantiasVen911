/**
 * notificaciones.js - Sistema de Notificaciones en Tiempo Real (SSE)
 * 
 * Implementa la comunicación unidireccional con el servidor mediante Server-Sent Events,
 * permitiendo la actualización dinámica de la bandeja de entrada sin recargar la página.
 * Soporta gestión de estados (leído/no leído) y formateo de tiempo relativo.
 */

(function () {
    'use strict';

    // 1. ESTADO INTERNO Y CONFIGURACIÓN VISUAL
    let websocket = null;
    const INTERVALO_RECONEXION_MS = 8000;
    
    // Diccionario de estilos e iconografía por tipo de notificación
    const iconosPorTipo = {
        alerta:         { clase: 'tipo-alerta',  icono: 'bi-exclamation-triangle-fill' },
        info:           { clase: 'tipo-info',    icono: 'bi-info-circle-fill'          },
        cambio_estado:  { clase: 'tipo-cambio',  icono: 'bi-arrow-repeat'              },
        default:        { clase: 'tipo-info',    icono: 'bi-bell-fill'                 },
    };

    // 2. REFERENCIAS AL DOM (ELEMENTOS NUCLEARES)
    const $badge    = document.getElementById('notif-badge');
    const $lista    = document.getElementById('notif-lista');
    const $vacio    = document.getElementById('notif-vacio');
    const $btnTodas = document.getElementById('btn-marcar-todas');

    // Validación de presencia del módulo en la vista actual
    if (!$badge) return;

    // 3. GESTIÓN DE CONEXIÓN WEBSOCKETS (POC RATCHET)
    function conectarWebSocket() {
        if (websocket) websocket.close();

        // Conexión al demonio Ratchet en el puerto 8080
        websocket = new WebSocket('ws://localhost:8080');

        websocket.onopen = function() {
            console.log('[Notificaciones] Conectado al servidor WebSocket (Ratchet).');
            // Registrar el usuario en el servidor para enrutamiento directo
            if (window.USUARIO_ID) {
                websocket.send(JSON.stringify({ action: 'registrar', usuario_id: window.USUARIO_ID }));
            }
        };

        // Recepción de mensajes en tiempo real
        websocket.onmessage = function (evento) {
            try {
                const notif = JSON.parse(evento.data);

                // El servidor ya enruta solo a este cliente. Validación mínima
                // para proteger contra mensajes malformados o del fallback broadcast.
                if (notif.usuario_id && parseInt(notif.usuario_id) !== window.USUARIO_ID) {
                    return;
                }

                // Mostrar alerta visual instantánea usando SweetAlert2
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: notif.titulo || 'Alerta',
                        text: notif.mensaje || 'Nueva notificación',
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                }
                
                // Agregamos la notificación al principio de la lista
                agregarNotificacionAlDOM(notif);
                actualizarBadgeManual(1);
                
            } catch (e) {
                console.warn('[Notificaciones] Error al procesar mensaje WS:', e);
            }
        };

        // Gestión de resiliencia ante caídas del Demonio
        websocket.onclose = function () {
            console.warn('[Notificaciones] Desconectado del WebSocket. Reintentando en ' + (INTERVALO_RECONEXION_MS/1000) + 's...');
            setTimeout(conectarWebSocket, INTERVALO_RECONEXION_MS);
        };
        
        websocket.onerror = function (error) {
            console.error('[Notificaciones] Error en WebSocket:', error);
            websocket.close(); // Fuerza el onclose para reconectar
        };
    }

    function agregarNotificacionAlDOM(notif) {
        $vacio.style.display = 'none';
        const li = document.createElement('li');
        li.className = 'notif-item no-leido';
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
        
        // Insertar al principio (después de notif-header si hubiera, o primero)
        if ($lista.firstChild) {
            $lista.insertBefore(li, $lista.firstChild);
        } else {
            $lista.appendChild(li);
        }
    }

    // 4. RENDERIZADO DINÁMICO DE LA BANDEJA DE NOTIFICACIONES
    function renderizarNotificaciones(notificaciones) {
        if (!Array.isArray(notificaciones)) return;

        const cantidad = notificaciones.length;

        // Actualización del contador visual (Badge)
        if (cantidad > 0) {
            $badge.textContent = cantidad > 99 ? '99+' : cantidad;
            $badge.classList.remove('d-none');
        } else {
            $badge.classList.add('d-none');
        }

        // Limpieza de la lista actual para evitar duplicidad
        const items = $lista.querySelectorAll('.notif-item');
        items.forEach(item => item.remove());

        // Manejo de estado vacío (Empty State)
        if (cantidad === 0) {
            $vacio.style.display = 'block';
            return;
        }

        $vacio.style.display = 'none';

        // Construcción iterativa de los elementos de la lista
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

            // Vinculación de evento para marcado individual
            li.addEventListener('click', () => marcarLeida(notif.id, li));
            $lista.insertBefore(li, $vacio);
        });
    }

    // 5. ACCIONES DE PERSISTENCIA (API FETCH)
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
        .catch(() => {}); // Fallo silencioso (Inercia Cero)
    }

    // Delegación de marcado masivo
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

    // 6. HELPERS DE FORMATO Y UI
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

    // 7. INICIALIZACIÓN DEL PROCESO
    function inicializarBandeja() {
        // 7.1 Carga inicial de notificaciones huérfanas/pendientes al abrir la página
        fetch('index.php?url=notificacion/obtenerPendientes')
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    renderizarNotificaciones(res.data);
                }
            })
            .catch(() => console.warn('[Notificaciones] Fallo carga inicial'))
            .finally(() => {
                // 7.2 Levantar la escucha en tiempo real (WebSockets)
                conectarWebSocket();
            });
    }

    inicializarBandeja();

})();
