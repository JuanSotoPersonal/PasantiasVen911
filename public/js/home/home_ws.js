/**
 * home_ws.js - Gestión del Estado del Servidor WebSocket
 * 
 * Este módulo se encarga de monitorizar la disponibilidad del demonio WS
 * y permitir su arranque remoto desde la interfaz del Dashboard.
 */
(function () {
    'use strict';

    const $indicador   = document.getElementById('ws-indicador');
    const $texto       = document.getElementById('ws-estado-texto');
    const $latencia    = document.getElementById('ws-latencia-texto');
    const $instruccion = document.getElementById('ws-instruccion');
    const $btnRefresh  = document.getElementById('btn-refrescar-ws');
    const $btnIniciar  = document.getElementById('btn-iniciar-ws');

    if (!$indicador) return; // Seguridad: solo corre si el widget existe

    let estaVerificando = false;

    function verificarEstadoWS() {
        // Bloqueo de peticiones concurrentes
        if (estaVerificando) return;
        estaVerificando = true;

        $texto.textContent = 'Verificando...';
        $indicador.style.background = '#6c757d';
        $latencia.textContent = '';

        fetch('index.php?url=notificacion/estadoServidor')
            .then(r => r.json())
            .then(data => {
                const wsActivo = data.ws_activo;
                const rabbitActivo = data.rabbit_activo;
                const workerActivo = data.worker_activo;

                if (wsActivo && rabbitActivo && workerActivo) {
                    $indicador.style.background = '#16a34a'; // Verde
                    $texto.textContent = '✓ Todos los servicios activos';
                    $latencia.textContent = `Latencia: ${data.latencia_ms} ms`;
                    if ($instruccion) $instruccion.style.display = 'none';
                } else {
                    $indicador.style.background = '#dc3545'; // Rojo
                    let msg = '✗ Servicios fuera de línea';
                    
                    if (!wsActivo && !rabbitActivo && !workerActivo) msg = '✗ Todo fuera de línea';
                    else if (!wsActivo) msg = '✗ WebSocket inactivo';
                    else if (!rabbitActivo) msg = '✗ RabbitMQ inactivo';
                    else if (!workerActivo) msg = '✗ Worker PHP caído';
                    
                    $texto.textContent = msg;
                    $latencia.textContent = !workerActivo ? 'Sin puente Worker' : (wsActivo ? 'RabbitMQ sin respuesta' : 'WebSocket sin respuesta');
                    if ($instruccion) $instruccion.style.display = 'block';
                }
            })
            .catch(() => {
                $indicador.style.background = '#ffc107'; // Amarillo
                $texto.textContent = '⚠ Error de comunicación';
                $latencia.textContent = '';
            })
            .finally(() => {
                estaVerificando = false;
            });
    }

    function iniciarServidorWS() {
        if ($btnIniciar) {
            $btnIniciar.disabled = true;
            $btnIniciar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Iniciando...';
        }

        fetch('index.php?url=notificacion/iniciarServidor')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Esperar 3 segundos a que el servidor levante antes de verificar
                    setTimeout(() => {
                        verificarEstadoWS();
                        if ($btnIniciar) {
                            $btnIniciar.disabled = false;
                            $btnIniciar.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar ahora';
                        }
                    }, 3000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo iniciar el servidor.'));
                    if ($btnIniciar) {
                        $btnIniciar.disabled = false;
                        $btnIniciar.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar ahora';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error fatal al intentar conectar con el controlador.');
                if ($btnIniciar) {
                    $btnIniciar.disabled = false;
                    $btnIniciar.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar ahora';
                }
            });
    }

    // Verificar al cargar la página
    verificarEstadoWS();

    // Botón de refresco manual
    if ($btnRefresh) {
        $btnRefresh.addEventListener('click', verificarEstadoWS);
    }

    // Botón de inicio manual
    if ($btnIniciar) {
        $btnIniciar.addEventListener('click', iniciarServidorWS);
    }

    // Auto-verificación cada 30 segundos
    setInterval(verificarEstadoWS, 30000);
})();
