<?php

require_once 'app/modelos/NotificacionModelo.php';
use App\modelos\NotificacionModelo;

class NotificacionControlador {

    private NotificacionModelo $modelo;

    public function __construct() {
        // Verificar sesión activa
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        $this->modelo = new NotificacionModelo();
    }

    //--------------------------------------------------------------------
    // SSE: Mantiene la conexión abierta y envía notificaciones no leídas
    //--------------------------------------------------------------------
    public function stream(): void {
        // Cabeceras para SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Necesario en algunos entornos Nginx/Apache

        // Leer datos de sesión necesarios y LIBERAR el lock de sesión
        // CRÍTICO: sin esto, el bucle SSE bloquea todas las demás peticiones
        // porque PHP mantiene el archivo de sesión bloqueado mientras el script vive.
        $usuario_id = (int)$_SESSION['user_id'];
        session_write_close(); // <-- Libera el lock; otras pestañas pueden cargar normalmente

        // Desactivar tiempo de ejecución máximo para conexiones persistentes
        set_time_limit(0);

        // Ciclo SSE: envía datos y espera
        while (true) {
            // Verificar que el cliente sigue conectado
            if (connection_aborted()) break;

            $notificaciones = $this->modelo->obtenerNoLeidas($usuario_id);

            echo "data: " . json_encode($notificaciones) . "\n\n";

            // Forzar envío inmediato al buffer de salida
            if (ob_get_level() > 0) ob_flush();
            flush();

            // Esperar 6 segundos antes del siguiente ciclo
            sleep(6);
        }
    }

    //--------------------------------------------------------------------
    // POST: Marca una notificación individual como leída
    //--------------------------------------------------------------------
    public function marcarLeida(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            return;
        }

        $id_notif   = (int)($_POST['id'] ?? 0);
        $usuario_id = (int)$_SESSION['user_id'];

        if (!$id_notif) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $resultado = $this->modelo->marcarLeida($id_notif, $usuario_id);
        echo json_encode(['success' => $resultado]);
    }

    //--------------------------------------------------------------------
    // POST: Marca TODAS las notificaciones del usuario como leídas
    //--------------------------------------------------------------------
    public function marcarTodas(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            return;
        }

        $usuario_id = (int)$_SESSION['user_id'];
        $resultado  = $this->modelo->marcarTodasLeidas($usuario_id);
        echo json_encode(['success' => $resultado]);
    }
}
