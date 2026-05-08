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

    /**
     * Envía una notificación a todos los usuarios que posean un rol específico.
     * Usa un INSERT batch para persistir todas las notificaciones en 1 query,
     * y un único emit al bus WebSocket con el array de destinatarios.
     *
     * @param int      $rolId   ID del rol destinatario (1: Admin, 2: Operador, 3: Despacho, 4: Jefatura)
     * @param string   $tipo    Categoría de la alerta (alerta, info, cambio_estado)
     * @param string   $titulo  Título corto de la notificación
     * @param string   $mensaje Cuerpo detallado del mensaje
     * @param int|null $fichaId ID de la ficha vinculada (opcional)
     */
    public static function enviarPorRol(int $rolId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null): void {
        try {
            $modelo   = self::obtenerModelo();
            $usuarios = $modelo->obtenerUsuariosPorRol($rolId);

            if (empty($usuarios)) {
                return;
            }

            // 1. Persistencia masiva en BD: 1 INSERT batch en lugar de N INSERTs
            $insertados = $modelo->crearBatch($usuarios, $tipo, $titulo, $mensaje, $fichaId);

            if (empty($insertados)) {
                return;
            }

            // 2. Un solo emit al bus WS con el array de destinatarios
            $fechaActual   = date('Y-m-d H:i:s');
            $destinatarios = array_map(function ($item) use ($tipo, $titulo, $mensaje, $fichaId, $fechaActual) {
                return [
                    'id'             => $item['id'],
                    'usuario_id'     => $item['usuario_id'],
                    'tipo'           => $tipo,
                    'titulo'         => $titulo,
                    'mensaje'        => $mensaje,
                    'fecha_creacion' => $fechaActual,
                    'ficha_id'       => $fichaId,
                ];
            }, $insertados);

            self::emitirSocket(['destinatarios' => $destinatarios]);

        } catch (\Exception $e) {
            error_log("[Notificador] Error en enviarPorRol: " . $e->getMessage());
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. ENVÍO INDIVIDUAL (sin cambios en lógica, usa el singleton)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Envía una notificación dirigida a un único usuario específico.
     *
     * @param int      $usuarioId ID del usuario receptor
     * @param string   $tipo      Categoría de la alerta
     * @param string   $titulo    Título corto
     * @param string   $mensaje   Cuerpo detallado
     * @param int|null $fichaId   ID de la ficha vinculada (opcional)
     */
    public static function enviarAUsuario(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null): void {
        try {
            $modelo  = self::obtenerModelo();
            $notifId = $modelo->crear($usuarioId, $tipo, $titulo, $mensaje, $fichaId);

            if ($notifId) {
                self::emitirSocket([
                    'id'             => $notifId,
                    'usuario_id'     => $usuarioId,
                    'tipo'           => $tipo,
                    'titulo'         => $titulo,
                    'mensaje'        => $mensaje,
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'ficha_id'       => $fichaId,
                ]);
            }
        } catch (\Exception $e) {
            error_log("[Notificador] Error en enviarAUsuario: " . $e->getMessage());
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. COMUNICACIÓN INTERNA CON EL BUS DE EVENTOS (RATCHET)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Emite un POST al servidor WebSocket interno (puerto 8081).
     * Timeout de 1s para no bloquear el request del despachador si el demonio está caído.
     */
    private static function emitirSocket(array $data): void {
        // Normalizar tipos escalares para JSON bien formado
        if (isset($data['id']))         $data['id']         = (int)$data['id'];
        if (isset($data['usuario_id'])) $data['usuario_id'] = (int)$data['usuario_id'];
        if (isset($data['ficha_id']))   $data['ficha_id']   = $data['ficha_id'] ? (int)$data['ficha_id'] : null;

        $payload = json_encode($data);
        $ws_host = getenv('WS_INTERNAL_HOST') ?: '127.0.0.1';
        $ch      = curl_init("http://{$ws_host}:8081");

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,    $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'Connection: close',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_exec($ch);
        curl_close($ch);
    }
}
