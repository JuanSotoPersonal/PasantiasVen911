<?php
/**
 * Componente: Widget de Estado de Notificaciones (WS + RabbitMQ)
 * Solo para Administradores. Permite monitorear y arrancar los servicios asíncronos.
 */
if ((int)($_SESSION['user_rol_id'] ?? 0) !== 1) return;
?>
<div class="card shadow-sm border-0 rounded-4" id="card-estado-ws">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title text-success fw-bold mb-0">
            <i class="bi bi-broadcast me-2"></i>Estado Sistema de Notificaciones
        </h3>
        <button class="btn btn-sm btn-outline-success rounded-pill" id="btn-refrescar-ws" title="Verificar ahora">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>
    <div class="card-body p-4">
        <!-- Indicador principal -->
        <div class="d-flex align-items-center gap-3 mb-3">
            <div id="ws-indicador" style="width:18px;height:18px;border-radius:50%;background:#6c757d;flex-shrink:0;"></div>
            <div>
                <div class="fw-bold fs-6" id="ws-estado-texto">Verificando...</div>
                <small class="text-muted" id="ws-latencia-texto"></small>
            </div>
        </div>
        <!-- Info técnica -->
        <div class="small text-muted border-top pt-3">
            <div class="mb-1"><i class="bi bi-hdd-network me-1"></i>WebSocket: <code>ws://localhost:8080</code></div>
            <div class="mb-1"><i class="bi bi-diagram-3 me-1"></i>RabbitMQ: <code>amqp://localhost:5672</code></div>
            <div><i class="bi bi-cpu me-1"></i>Worker PHP: <code>consumidor_notif.php</code></div>
        </div>
        <!-- Instrucción de arranque -->
        <div class="mt-3 p-3 rounded-3 bg-light border" id="ws-instruccion" style="display:none;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                    <span class="small fw-bold">Servicios fuera de línea</span>
                </div>
                <button class="btn btn-xs btn-success rounded-pill px-3 shadow-sm" id="btn-iniciar-ws">
                    <i class="bi bi-play-fill"></i> Iniciar ahora
                </button>
            </div>
            <code class="d-block mt-2 small text-muted">Inicio automático en 2do plano</code>
        </div>
    </div>
</div>
