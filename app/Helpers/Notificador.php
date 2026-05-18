<?php
/**
 * HELPER: Notificador
 * Propósito: Centralizar la emisión de notificaciones en el sistema.
 *
 * ARQUITECTURA DESACOPLADA:
 * - Persistencia en BD: SIEMPRE síncrona e inmediata. No depende de ningún servicio externo.
 * - Tiempo real (WebSocket): Best-effort vía RabbitMQ. Si el broker no está disponible o el
 *   worker no corre, la notificación ya está en BD y se verá al recargar.
 *
 * FLUJO:
 *   enviar*() → guardar en BD (directo) → intentar encolar payload en RabbitMQ (para WS)
 *   Worker → recibe de RabbitMQ → reenvía al servidor WebSocket (sin tocar BD)
 */

namespace App\Helpers;

class Notificador {

    // ///////////////////////////////////////////////////////////////////
    // 1. ENVÍO MASIVO POR ROL
    // ///////////////////////////////////////////////////////////////////

    /**
     * Persiste notificaciones para todos los usuarios de un rol
     * y luego intenta notificarlos en tiempo real vía WebSocket.
     */
    public static function enviarPorRol(int $rolId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null, array $excluirUsuarios = []): array {
        $notificados = [];
        try {
            require_once dirname(__DIR__) . '/modelos/NotificacionModelo.php';
            $modelo   = new \App\modelos\NotificacionModelo();
            $usuarios = $modelo->obtenerUsuariosPorRol($rolId);

            // Garantizar que el Administrador (Rol 1) siempre reciba una copia de las alertas por rol
            if ($rolId !== 1) {
                $admins = $modelo->obtenerUsuariosPorRol(1);
                $usuarios = array_values(array_unique(array_merge($usuarios, $admins)));
            }

            // Excluir los usuarios solicitados
            if (!empty($excluirUsuarios)) {
                $usuarios = array_filter($usuarios, function($uid) use ($excluirUsuarios) {
                    return !in_array((int)$uid, $excluirUsuarios, true);
                });
                $usuarios = array_values($usuarios);
            }

            if (empty($usuarios)) return [];

            // 1. Persistencia garantizada en BD (batch insert)
            $insertados = $modelo->crearBatch($usuarios, $tipo, $titulo, $mensaje, $fichaId);

            // 2. Best-effort: encolar cada notificación para envío WebSocket
            $fechaActual = date('Y-m-d H:i:s');
            foreach ($insertados as $item) {
                $uid = (int)$item['usuario_id'];
                $notificados[] = $uid;
                self::encolarParaWebSocket([
                    'id'             => $item['id'],
                    'usuario_id'     => $uid,
                    'tipo'           => $tipo,
                    'titulo'         => $titulo,
                    'mensaje'        => $mensaje,
                    'ficha_id'       => $fichaId,
                    'fecha_creacion' => $fechaActual,
                ]);
            }
        } catch (\Throwable $e) {
            error_log("[Notificador] Error en enviarPorRol: " . $e->getMessage());
        }
        return $notificados;
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. ENVÍO INDIVIDUAL
    // ///////////////////////////////////////////////////////////////////

    /**
     * Persiste una notificación para un usuario específico
     * y luego intenta notificarlo en tiempo real vía WebSocket.
     */
    public static function enviarAUsuario(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null, array $excluirUsuarios = []): array {
        $notificados = [];
        try {
            require_once dirname(__DIR__) . '/modelos/NotificacionModelo.php';
            $modelo  = new \App\modelos\NotificacionModelo();

            // 1. Persistencia garantizada en BD para el usuario destinatario (si no está excluido)
            if (!in_array($usuarioId, $excluirUsuarios, true)) {
                $notifId = $modelo->crear($usuarioId, $tipo, $titulo, $mensaje, $fichaId);

                // 2. Best-effort: encolar para envío WebSocket
                if ($notifId) {
                    $notificados[] = $usuarioId;
                    self::encolarParaWebSocket([
                        'id'             => $notifId,
                        'usuario_id'     => $usuarioId,
                        'tipo'           => $tipo,
                        'titulo'         => $titulo,
                        'mensaje'        => $mensaje,
                        'ficha_id'       => $fichaId,
                        'fecha_creacion' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Garantizar que el Administrador (Rol 1) siempre reciba una copia de las alertas directas (si no está excluido)
            $admins = $modelo->obtenerUsuariosPorRol(1);
            foreach ($admins as $adminId) {
                $adminIdInt = (int)$adminId;
                if ($adminIdInt !== $usuarioId && !in_array($adminIdInt, $excluirUsuarios, true) && !in_array($adminIdInt, $notificados, true)) {
                    $adminNotifId = $modelo->crear($adminIdInt, $tipo, $titulo, $mensaje, $fichaId);
                    if ($adminNotifId) {
                        $notificados[] = $adminIdInt;
                        self::encolarParaWebSocket([
                            'id'             => $adminNotifId,
                            'usuario_id'     => $adminIdInt,
                            'tipo'           => $tipo,
                            'titulo'         => $titulo,
                            'mensaje'        => $mensaje,
                            'ficha_id'       => $fichaId,
                            'fecha_creacion' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("[Notificador] Error en enviarAUsuario: " . $e->getMessage());
        }
        return $notificados;
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. REENVÍO AL WEBSOCKET VÍA RABBITMQ (Best-effort, sin BD)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Encola un payload ya persistido en BD para que el Worker lo reenvíe
     * al servidor WebSocket. Falla silenciosamente si RabbitMQ no está disponible;
     * la notificación ya fue guardada en BD, así que no hay pérdida de datos.
     */
    private static function encolarParaWebSocket(array $payloadWS): void {
        try {
            require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

            $rabbitHost = getenv('RABBITMQ_HOST') ?: '127.0.0.1';
            $rabbitPort = getenv('RABBITMQ_PORT') ?: 5672;
            $rabbitUser = getenv('RABBITMQ_USER') ?: 'ven911';
            $rabbitPass = getenv('RABBITMQ_PASS') ?: 'ven911_mq_pass';
            $queueName  = getenv('RABBITMQ_QUEUE') ?: 'notificaciones';

            $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
            $channel    = $connection->channel();
            $channel->queue_declare($queueName, false, true, false, false);

            // El payload ya tiene el ID de BD — el worker solo reenvía al WS sin tocar BD
            $msg = new \PhpAmqpLib\Message\AMQPMessage(
                json_encode(['action' => 'ws_forward', 'payload' => $payloadWS]),
                ['delivery_mode' => 2]
            );
            $channel->basic_publish($msg, '', $queueName);
            $channel->close();
            $connection->close();
        } catch (\Throwable $e) {
            // RabbitMQ no disponible: sin tiempo real, pero BD ya fue guardada
            error_log("[Notificador] WebSocket best-effort falló: " . $e->getMessage());
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. ENCOLAR TRABAJO GENÉRICO (auditoría, etc.)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Publica un trabajo arbitrario en RabbitMQ (usado por EventoModelo para auditoría).
     */
    public static function encolarTrabajo(array $payload): void {
        try {
            require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

            $rabbitHost = getenv('RABBITMQ_HOST') ?: '127.0.0.1';
            $rabbitPort = getenv('RABBITMQ_PORT') ?: 5672;
            $rabbitUser = getenv('RABBITMQ_USER') ?: 'ven911';
            $rabbitPass = getenv('RABBITMQ_PASS') ?: 'ven911_mq_pass';
            $queueName  = getenv('RABBITMQ_QUEUE') ?: 'notificaciones';

            $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
            $channel    = $connection->channel();
            $channel->queue_declare($queueName, false, true, false, false);

            $msg = new \PhpAmqpLib\Message\AMQPMessage(json_encode($payload), ['delivery_mode' => 2]);
            $channel->basic_publish($msg, '', $queueName);
            $channel->close();
            $connection->close();
        } catch (\Throwable $e) {
            error_log("[Notificador] Error encolando trabajo genérico: " . $e->getMessage());
        }
    }
}
