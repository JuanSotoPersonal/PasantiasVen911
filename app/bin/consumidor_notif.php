<?php
/**
 * consumidor_notif.php - Worker Consumer (RabbitMQ)
 * Consume mensajes de RabbitMQ y los retransmite al bus WebSocket interno (:8081)
 */

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 1. Configuración desde variables de entorno
$rabbitHost = getenv('RABBITMQ_HOST') ?: '127.0.0.1';
$rabbitPort = getenv('RABBITMQ_PORT') ?: 5672;
$rabbitUser = getenv('RABBITMQ_USER') ?: 'ven911';
$rabbitPass = getenv('RABBITMQ_PASS') ?: 'ven911_mq_pass';
$queueName  = getenv('RABBITMQ_QUEUE') ?: 'notificaciones';
$wsHost     = getenv('WS_INTERNAL_HOST') ?: '127.0.0.1';

echo "[*] Iniciando worker de notificaciones...\n";

// 2. Conexión AMQP (Retry Mechanism)
$connection = null;
$channel = null;
$maxRetries = 5;
$retryDelay = 3;

for ($i = 0; $i < $maxRetries; $i++) {
    try {
        $connection = new AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
        $channel = $connection->channel();
        break; // Conexión exitosa
    } catch (\Exception $e) {
        echo "[!] Intento ".($i+1)." fallido conectando a RabbitMQ. Reintentando en {$retryDelay}s...\n";
        sleep($retryDelay);
    }
}

if (!$connection || !$channel) {
    echo "[!] Error fatal conectando a RabbitMQ despues de {$maxRetries} intentos.\n";
    exit(1);
}

// 3. Declarar cola (durable, no exclusiva, no auto-delete)
$channel->queue_declare($queueName, false, true, false, false);

echo "[*] Conectado a RabbitMQ. Esperando mensajes en la cola '{$queueName}'.\n";

// 4. Callback de procesamiento
$callback = function (AMQPMessage $msg) use ($wsHost) {
    $payload = $msg->body;
    $datos = json_decode($payload, true);
    echo "[x] Mensaje recibido: " . substr($payload, 0, 80) . "...\n";

    $action = $datos['action'] ?? 'notificacion';

    // ///////////////////////////////////////////////////////////////
    // ws_forward: Payload ya persistido en BD, solo reenviar al WS
    // ///////////////////////////////////////////////////////////////
    if ($action === 'ws_forward') {
        $payload = json_encode($datos['payload']);
        echo "[*] ws_forward: reenviando al WebSocket...\n";
        // Cae al bloque cURL al final del callback
    }

    if ($action === 'registrar_auditoria_sistema') {
        echo "[*] Registrando auditoría de sistema en BD...\n";
        try {
            require_once dirname(__DIR__) . '/modelos/EventoModelo.php';
            $modelo = new \App\modelos\EventoModelo();
            $d = $datos['datos'];
            $modelo->insertarEventoSistemaReal(
                $d['usuario_id'], $d['tipo_accion'], $d['tabla'], 
                $d['registro_id'], $d['anterior'], $d['nuevo'], $d['descripcion']
            );
            echo "[v] Auditoría de sistema registrada.\n";
            $msg->ack();
            return; // Termina aquí, no se reenvía por WebSocket
        } catch (\Exception $e) {
            echo "[!] Error guardando auditoría de sistema: " . $e->getMessage() . "\n";
            $msg->ack();
            return;
        }
    }

    if ($action === 'registrar_auditoria_ficha') {
        echo "[*] Registrando auditoría de ficha en BD...\n";
        try {
            require_once dirname(__DIR__) . '/modelos/EventoModelo.php';
            $modelo = new \App\modelos\EventoModelo();
            $d = $datos['datos'];
            $modelo->insertarEventoFichaReal(
                $d['ficha_id'], $d['usuario_id'], $d['tipo_evento'], 
                $d['estado_anterior'], $d['estado_nuevo'], 
                $d['anterior'], $d['nuevo'], $d['descripcion']
            );
            echo "[v] Auditoría de ficha registrada.\n";
            $msg->ack();
            return; // Termina aquí, no se reenvía por WebSocket
        } catch (\Exception $e) {
            echo "[!] Error guardando auditoría de ficha: " . $e->getMessage() . "\n";
            $msg->ack();
            return;
        }
    }

    if ($action === 'guardar_notificacion_rol') {
        echo "[*] Guardando notificación masiva en BD...\n";
        try {
            require_once dirname(__DIR__) . '/modelos/NotificacionModelo.php';
            $modelo = new \App\modelos\NotificacionModelo();
            $usuarios = $modelo->obtenerUsuariosPorRol($datos['rol_id']);
            
            if (empty($usuarios)) {
                $msg->ack();
                return;
            }

            $insertados = $modelo->crearBatch($usuarios, $datos['tipo'], $datos['titulo'], $datos['mensaje'], $datos['ficha_id']);
            
            if (empty($insertados)) {
                $msg->ack();
                return;
            }

            $fechaActual = date('Y-m-d H:i:s');
            $destinatarios = array_map(function ($item) use ($datos, $fechaActual) {
                return [
                    'id'             => $item['id'],
                    'usuario_id'     => $item['usuario_id'],
                    'tipo'           => $datos['tipo']    ?? 'info',
                    'titulo'         => $datos['titulo']  ?? 'Notificación',
                    'mensaje'        => $datos['mensaje'] ?? 'Nueva actividad registrada.',
                    'fecha_creacion' => $fechaActual,
                    'ficha_id'       => $datos['ficha_id'] ?? null,
                ];
            }, $insertados);

            // Reemplazar el payload original con el payload para el WebSockets
            $payload = json_encode(['destinatarios' => $destinatarios]);
            echo "[v] Notificación masiva guardada. Reenviando al WS...\n";

        } catch (\Exception $e) {
            echo "[!] Error guardando notificación masiva: " . $e->getMessage() . "\n";
            $msg->ack();
            return;
        }
    }

    if ($action === 'guardar_notificacion_usuario') {
        echo "[*] Guardando notificación individual en BD...\n";
        try {
            require_once dirname(__DIR__) . '/modelos/NotificacionModelo.php';
            $modelo = new \App\modelos\NotificacionModelo();
            
            $notifId = $modelo->crear($datos['usuario_id'], $datos['tipo'], $datos['titulo'], $datos['mensaje'], $datos['ficha_id']);

            if (!$notifId) {
                $msg->ack();
                return;
            }

            $payload = json_encode([
                'id'             => $notifId,
                'usuario_id'     => $datos['usuario_id'],
                'tipo'           => $datos['tipo'],
                'titulo'         => $datos['titulo'],
                'mensaje'        => $datos['mensaje'],
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'ficha_id'       => $datos['ficha_id'],
            ]);
            echo "[v] Notificación individual guardada. Reenviando al WS...\n";

        } catch (\Exception $e) {
            echo "[!] Error guardando notificación individual: " . $e->getMessage() . "\n";
            $msg->ack();
            return;
        }
    }

    // Si es una notificación normal, la reenvía al servidor_ws.php vía cURL (:8081)
    $ch = curl_init("http://{$wsHost}:8081");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload),
        'Connection: close',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Aumentado para payloads base64 grandes

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) { // Accept 200 or 201
        // Entregado correctamente, ACK al mensaje
        $msg->ack();
        echo "[v] Entregado a WS.\n";
    } else {
        // Falló el servidor WS, NACK para reencolar y reintentar luego
        echo "[!] Falló entrega a WS (HTTP {$httpCode}). Reencolando...\n";
        $msg->nack(true);
        // Pequeño sleep para no inundar si el WS está caído
        sleep(2);
    }
};

// 5. Configurar QoS (procesar 1 mensaje a la vez) y empezar a consumir
$channel->basic_qos(null, 1, null);
$channel->basic_consume($queueName, '', false, false, false, false, $callback);

// 6. Bucle infinito
try {
    while ($channel->is_open()) {
        $channel->wait();
    }
} catch (\Exception $e) {
    echo "[!] Error en consumo: " . $e->getMessage() . "\n";
}

$channel->close();
$connection->close();
