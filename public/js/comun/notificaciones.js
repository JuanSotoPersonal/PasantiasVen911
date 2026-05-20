/**
 * notificaciones.js - Sistema de Notificaciones en Tiempo Real (SSE / WebSockets)
 * 
 * Implementa la comunicación con el servidor mediante WebSockets,
 * permitiendo la actualización dinámica de la bandeja de entrada sin recargar la página.
 * Soporta gestión de estados (leído/no leído), alertas en tiempo real y formateo de tiempo.
 */

(function () {
    'use strict';

    // 1. ESTADO INTERNO Y CONFIGURACIÓN VISUAL
    let websocket = null;
    const INTERVALO_RECONEXION_MS = 8000;
    
    // Diccionario de estilos e iconografía por tipo de notificación
    const iconosPorTipo = {
        alerta: { clase: 'tipo-alerta', icono: 'bi-exclamation-triangle-fill' },
        info: { clase: 'tipo-info', icono: 'bi-info-circle-fill' },
        cambio_estado: { clase: 'tipo-cambio', icono: 'bi-arrow-repeat' },
        critico: { clase: 'tipo-critico', icono: 'bi-x-octagon-fill' },
        default: { clase: 'tipo-info', icono: 'bi-bell-fill' },
    };

    // 2. REFERENCIAS AL DOM (ELEMENTOS NUCLEARES)
    const $badge = document.getElementById('notif-badge');
    const $lista = document.getElementById('notif-lista');
    const $vacio = document.getElementById('notif-vacio');
    const $btnTodas = document.getElementById('btn-marcar-todas');

    // Validación de presencia del módulo en la vista actual
    if (!$badge) return;

    // 3. GESTIÓN DE CONEXIÓN WEBSOCKETS (POC RATCHET)
    function conectarWebSocket() {
        if (websocket) websocket.close();

        // Conexión al demonio Ratchet en el puerto 8080 (Dinámico usando el hostname actual)
        websocket = new WebSocket(`ws://${window.location.hostname}:8080`);

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

                // Mostrar alerta visual flotante (Toast) usando SweetAlert2
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                    
                    let toastIcon = 'info';
                    if (notif.tipo === 'alerta') toastIcon = 'warning';
                    else if (notif.tipo === 'critico') toastIcon = 'error';
                    else if (notif.tipo === 'cambio_estado') toastIcon = 'success';

                    Toast.fire({
                        icon: toastIcon,
                        title: window.escapeHTML(notif.titulo || 'Notificación'),
                        text: window.escapeHTML(notif.mensaje || 'Nueva actividad registrada.')
                    });
                }
                
                // Agregamos la notificación al principio de la lista
                agregarNotificacionAlDOM(notif);
                actualizarBadgeManual(1);
                
            } catch (err) {
                console.error('Error al procesar notificación Socket:', err, evento.data);
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
            <div class="notif-content flex-grow-1">
                <div class="notif-header d-flex justify-content-between align-items-baseline gap-2">
                    <span class="notif-titulo fw-bold text-dark text-truncate" style="max-width: 190px;" title="${window.escapeHTML(notif.titulo || 'Notificación')}">${window.escapeHTML(notif.titulo || 'Notificación')}</span>
                    <span class="notif-tiempo small text-muted text-nowrap">${tiempo}</span>
                </div>
                <p class="notif-mensaje mb-0 text-muted small text-wrap">${window.escapeHTML(notif.mensaje || 'Nueva actividad registrada.')}</p>
            </div>
        `;
        li.addEventListener('click', () => {
            marcarLeida(notif.id, li);
            if (notif.ficha_id) {
                window.location.href = `index.php?url=ficha&accion=ver&id=${notif.ficha_id}`;
            }
        });
        
        // Insertar al principio (después de vacio o primero)
        if ($lista.firstChild) {
            $lista.insertBefore(li, $lista.firstChild);
        } else {
            $lista.appendChild(li);
        }
    }

    // 4. RENDERIZADO DINÁMICO DE LA BANDEJA DE NOTIFICACIONES
    function renderizarNotificaciones(notificaciones) {
        if (!Array.isArray(notificaciones)) return;

        const noLeidas = notificaciones.filter(n => n.leido == 0).length;
        const total = notificaciones.length;

        // Actualización del contador visual (Badge) - Solo No Leídas
        if (noLeidas > 0) {
            $badge.textContent = noLeidas > 99 ? '99+' : noLeidas;
            $badge.classList.remove('d-none');
        } else {
            $badge.classList.add('d-none');
        }

        // Limpieza de la lista actual para evitar duplicidad
        const items = $lista.querySelectorAll('.notif-item');
        items.forEach(item => item.remove());

        // Manejo de estado vacío (Empty State)
        if (total === 0) {
            $vacio.style.display = 'flex';
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
                <div class="notif-content flex-grow-1">
                    <div class="notif-header d-flex justify-content-between align-items-baseline gap-2">
                        <span class="notif-titulo fw-bold text-dark text-truncate" style="max-width: 190px;" title="${window.escapeHTML(notif.titulo || 'Notificación')}">${window.escapeHTML(notif.titulo || 'Notificación')}</span>
                        <span class="notif-tiempo small text-muted text-nowrap">${tiempo}</span>
                    </div>
                    <p class="notif-mensaje mb-0 text-muted small text-wrap">${window.escapeHTML(notif.mensaje || 'Nueva actividad registrada.')}</p>
                </div>
            `;
            // Vinculación de evento para marcado individual y navegación
            li.addEventListener('click', () => {
                marcarLeida(notif.id, li);
                if (notif.ficha_id) {
                    window.location.href = `index.php?url=ficha&accion=ver&id=${notif.ficha_id}`;
                }
            });
            $lista.appendChild(li);
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
            .catch(() => { });
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
        if (!fechaStr) return 'Reciente';
        
        // Reemplazar espacios por 'T' para que sea compatible con navegadores de iOS/Safari
        const fechaStrISO = fechaStr.replace(' ', 'T');
        const fecha = new Date(fechaStrISO);
        if (isNaN(fecha.getTime())) return 'Reciente';

        const diff = Math.floor((Date.now() - fecha.getTime()) / 1000);
        
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

    // Cargar pendientes si el DOM ya está listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarBandeja);
    } else {
        inicializarBandeja();
    }

    // Exponer la función globalmente para actualizar el badge de notificaciones desde otros scripts si es necesario
    window.cargarNotificacionesPendientes = function() {
        fetch('index.php?url=notificacion/obtenerPendientes')
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    renderizarNotificaciones(res.data);
                }
            })
            .catch(() => {});
    };

})();
