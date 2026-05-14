<?php
/**
 * HELPER: Notificador
 * Propósito: Centralizar la emisión de notificaciones en el sistema, manejando
 * la persistencia en base de datos y el envío en tiempo real vía WebSockets.
 *
 * OPTIMIZACIONES APLICADAS:
 * - Singleton de conexión: una sola instancia de NotificacionModelo por request
 *   (antes se instanciaba una nueva conexión PDO por cada llamada al helper).
 * - Batch INSERT: enviarPorRol() agrupa todos los INSERTs en una sola consulta.
 * - Un solo curl: enviarPorRol() emite un único POST al bus WS con todos los
 *   destinatarios, en lugar de un curl por usuario.
 */

namespace App\Helpers;

use App\modelos\NotificacionModelo;

require_once __DIR__ . '/../modelos/NotificacionModelo.php';

class Notificador {

    // ///////////////////////////////////////////////////////////////////
    // 1. SINGLETON DE CONEXIÓN
    //    Una sola instancia del modelo por ciclo de vida del request.
    //    Evita abrir N conexiones PDO para N llamadas al Notificador.
    // ///////////////////////////////////////////////////////////////////

    private static ?NotificacionModelo $modelo = null;

    private static function obtenerModelo(): NotificacionModelo {
        if (self::$modelo === null) {
            self::$modelo = new NotificacionModelo();
        }
        return self::$modelo;
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. ENVÍO MASIVO POR ROL (OPTIMIZADO: batch INSERT + 1 curl)
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
    // 3. ENVÍO INDIVIDUAL (sin cambios en lógica, usa el singleton)
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
    // 4. COMUNICACIÓN ASÍNCRONA CON RABBITMQ
    // ///////////////////////////////////////////////////////////////////

    /**
     * Publica el payload en RabbitMQ de forma asíncrona (fire-and-forget).
     * Reemplaza el cURL directo al servidor WS para evitar bloqueos.
     */
    private static function emitirSocket(array $data): void {
        // Normalizar tipos escalares para JSON bien formado
        if (isset($data['id']))         $data['id']         = (int)$data['id'];
        if (isset($data['usuario_id'])) $data['usuario_id'] = (int)$data['usuario_id'];
        if (isset($data['ficha_id']))   $data['ficha_id']   = $data['ficha_id'] ? (int)$data['ficha_id'] : null;

        $payload = json_encode($data);

        try {
            require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
            
            $rabbitHost = getenv('RABBITMQ_HOST') ?: '127.0.0.1';
            $rabbitPort = getenv('RABBITMQ_PORT') ?: 5672;
            $rabbitUser = getenv('RABBITMQ_USER') ?: 'ven911';
            $rabbitPass = getenv('RABBITMQ_PASS') ?: 'ven911_mq_pass';
            $queueName  = getenv('RABBITMQ_QUEUE') ?: 'notificaciones';

            $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
            $channel = $connection->channel();
            
            // Declarar cola (por si no existe)
            $channel->queue_declare($queueName, false, true, false, false);
            
            $msg = new \PhpAmqpLib\Message\AMQPMessage($payload, ['delivery_mode' => 2]); // Persistente
            $channel->basic_publish($msg, '', $queueName);
            
            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            error_log("[Notificador AMQP] Fallo silencioso enviando a RabbitMQ: " . $e->getMessage());
        }
    }

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
            error_log("[Notificador AMQP] Fallo encolando trabajo: " . $e->getMessage());
        }
    }
}
