<?php
/**
 * HELPER: Notificador
 * Propósito: Centralizar la emisión de notificaciones en el sistema, manejando
 * la persistencia en base de datos y el envío en tiempo real vía WebSockets.
 */

namespace App\Helpers;

use App\modelos\NotificacionModelo;

require_once __DIR__ . '/../modelos/NotificacionModelo.php';

class Notificador {

    /**
     * Envía una notificación a todos los usuarios que posean un rol específico.
     * 
     * @param int $rolId ID del rol destinatario (1: Admin, 2: Operador, 3: Despacho, 4: Jefatura)
     * @param string $tipo Categoría de la alerta (alerta, info, éxito)
     * @param string $titulo Título corto de la notificación
     * @param string $mensaje Cuerpo detallado del mensaje
     * @param int|null $fichaId ID de la ficha vinculada (opcional)
     */
    public static function enviarPorRol(int $rolId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null): void {
        try {
            $modelo = new NotificacionModelo();
            $usuarios = $modelo->obtenerUsuariosPorRol($rolId);
            
            // Persistencia masiva en BD
            foreach ($usuarios as $uid) {
                $notifId = $modelo->crear($uid, $tipo, $titulo, $mensaje, $fichaId);
                
                // Emisión al bus de eventos WebSocket (específico para cada usuario)
                if ($notifId) {
                    self::emitirSocket([
                        'id'         => $notifId,
                        'usuario_id' => (int)$uid,
                        'tipo'       => $tipo,
                        'titulo'     => $titulo,
                        'mensaje'    => $mensaje,
                        'fecha_creacion' => date('Y-m-d H:i:s'),
                        'ficha_id'   => $fichaId
                    ]);
                }
            }
        } catch (\Exception $e) {
            error_log("[Notificador] Error en enviarPorRol: " . $e->getMessage());
        }
    }

    /**
     * Envía una notificación dirigida a un único usuario específico.
     * 
     * @param int $usuarioId ID del usuario receptor
     * @param string $tipo Categoría de la alerta
     * @param string $titulo Título corto
     * @param string $mensaje Cuerpo detallado
     * @param int|null $fichaId ID de la ficha vinculada (opcional)
     */
    public static function enviarAUsuario(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?int $fichaId = null): void {
        try {
            $modelo = new NotificacionModelo();
            
            // Persistencia en BD
            $notifId = $modelo->crear($usuarioId, $tipo, $titulo, $mensaje, $fichaId);

            // Emisión al bus de eventos WebSocket
            if ($notifId) {
                self::emitirSocket([
                    'id'         => $notifId,
                    'usuario_id' => $usuarioId,
                    'tipo'       => $tipo,
                    'titulo'     => $titulo,
                    'mensaje'    => $mensaje,
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'ficha_id'   => $fichaId
                ]);
            }
        } catch (\Exception $e) {
            error_log("[Notificador] Error en enviarAUsuario: " . $e->getMessage());
        }
    }

    /**
     * Comunicación interna con el servidor de eventos (Ratchet/ReactPHP).
     */
    private static function emitirSocket(array $data): void {
        // Aseguramos que los tipos de datos sean correctos para el JSON
        if (isset($data['id']))         $data['id']         = (int)$data['id'];
        if (isset($data['usuario_id'])) $data['usuario_id'] = (int)$data['usuario_id'];
        if (isset($data['rol_id']))     $data['rol_id']     = (int)$data['rol_id'];
        if (isset($data['ficha_id']))   $data['ficha_id']   = $data['ficha_id'] ? (int)$data['ficha_id'] : null;

        $payload = json_encode($data);
        $ch = curl_init('http://127.0.0.1:8081');
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'Connection: close'
        ]);
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_exec($ch);
        curl_close($ch);
    }
}
