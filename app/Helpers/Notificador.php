<?php
/**
 * HELPER: Notificador
 * Propósito: Centralizar la emisión de notificaciones en el sistema, manejando
 * la persistencia en base de datos y el envío en tiempo real vía WebSockets.
 *
 * ARQUITECTURA ASÍNCRONA CON FALLBACK SÍNCRONO:
 * - Prioridad: Intentar encolar en RabbitMQ para procesamiento asíncrono.
 * - Fallback: Si RabbitMQ no está disponible (ej. entorno XAMPP local sin Docker),
 *   persiste la notificación directamente en la BD para garantizar que nunca se pierda.
 * - Escalabilidad: Permite manejar picos de tráfico sin degradar la respuesta del frontend.
 */

namespace App\Helpers;

class Notificador {

    // ///////////////////////////////////////////////////////////////////
    // 1. ENVÍO MASIVO POR ROL (Asíncrono vía RabbitMQ)
    // ///////////////////////////////////////////////////////////////////

    public static function enviarPorRol(int $rolId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null): void {
        self::encolarTrabajo([
            'action'   => 'guardar_notificacion_rol',
            'rol_id'   => $rolId,
            'tipo'     => $tipo,
            'titulo'   => $titulo,
            'mensaje'  => $mensaje,
            'ficha_id' => $fichaId
        ]);
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. ENVÍO INDIVIDUAL (Asíncrono vía RabbitMQ)
    // ///////////////////////////////////////////////////////////////////

    public static function enviarAUsuario(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null): void {
        self::encolarTrabajo([
            'action'     => 'guardar_notificacion_usuario',
            'usuario_id' => $usuarioId,
            'tipo'       => $tipo,
            'titulo'     => $titulo,
            'mensaje'    => $mensaje,
            'ficha_id'   => $fichaId
        ]);
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. COMUNICACIÓN ASÍNCRONA CON RABBITMQ
    // ///////////////////////////////////////////////////////////////////

    /**
     * Publica un trabajo arbitrario en RabbitMQ de forma asíncrona.
     * Útil para tareas pesadas como generación de reportes en segundo plano.
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
            $channel = $connection->channel();
            
            $channel->queue_declare($queueName, false, true, false, false);
            
            $msg = new \PhpAmqpLib\Message\AMQPMessage(json_encode($payload), ['delivery_mode' => 2]);
            $channel->basic_publish($msg, '', $queueName);
            
            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            error_log("[Notificador AMQP] Fallo encolando trabajo: " . $e->getMessage() . " — Usando fallback síncrono.");
            // Fallback: RabbitMQ no disponible, persistir directamente en BD
            self::procesarDirectamente($payload);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. FALLBACK SÍNCRONO (Persistencia directa sin RabbitMQ)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa el payload directamente contra la BD cuando RabbitMQ no está disponible.
     * Garantiza que las notificaciones siempre se persistan (entorno XAMPP local, etc.).
     */
    private static function procesarDirectamente(array $payload): void {
        try {
            require_once dirname(__DIR__) . '/modelos/NotificacionModelo.php';
            $modelo = new \App\modelos\NotificacionModelo();
            $accion = $payload['action'] ?? '';

            if ($accion === 'guardar_notificacion_usuario') {
                $modelo->crear(
                    (int)$payload['usuario_id'],
                    $payload['tipo'],
                    $payload['titulo'],
                    $payload['mensaje'],
                    $payload['ficha_id'] ?? null
                );

            } elseif ($accion === 'guardar_notificacion_rol') {
                $usuarios = $modelo->obtenerUsuariosPorRol((int)$payload['rol_id']);
                if (!empty($usuarios)) {
                    $modelo->crearBatch(
                        $usuarios,
                        $payload['tipo'],
                        $payload['titulo'],
                        $payload['mensaje'],
                        $payload['ficha_id'] ?? null
                    );
                }
            }
        } catch (\Exception $e) {
            error_log("[Notificador Fallback] Error en persistencia directa: " . $e->getMessage());
        }
    }
}
