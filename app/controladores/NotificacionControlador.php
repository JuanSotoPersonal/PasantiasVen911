<?php
/**
 * CONTROLADOR: NotificacionControlador
 * Propósito: Gestionar la transmisión de notificaciones en tiempo real (SSE)
 * y el control de estado de lectura de las alertas del sistema.
 */

require_once 'app/modelos/NotificacionModelo.php';
use App\modelos\NotificacionModelo;

class NotificacionControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private NotificacionModelo $modelo;

    /**
     * Constructor: Valida la sesión activa e inicializa el modelo.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        $this->modelo = new NotificacionModelo();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. STREAMING (REAL-TIME SSE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Mantiene una conexión persistente (Server-Sent Events) con el cliente
     * para enviar notificaciones push sin recargar la página.
     */
    public function stream(): void {
        // 2.1 Cabeceras obligatorias para el protocolo SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); 

        // 2.2 MANEJO DE SESIÓN (CRÍTICO)
        // PHP bloquea el archivo de sesión por defecto. Debemos leer el ID 
        // y cerrar la escritura inmediatamente para no bloquear otras peticiones.
        $usuario_id = (int)$_SESSION['user_id'];
        session_write_close(); 

        // 2.3 Desactivar el timeout del servidor para conexiones infinitas
        set_time_limit(0);

        // 2.4 Ciclo de vida de la conexión
        while (true) {
            // Verificar si el navegador cerró la pestaña
            if (connection_aborted()) break;

            $notificaciones = $this->modelo->obtenerNoLeidas($usuario_id);

            // Formato estándar SSE: "data: [JSON]\n\n"
            echo "data: " . json_encode($notificaciones) . "\n\n";

            // Forzar el vaciado del buffer de salida hacia el cliente
            if (ob_get_level() > 0) ob_flush();
            flush();

            // Intervalo de consulta: 6 segundos para equilibrio rendimiento/frecuencia
            sleep(6);
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. GESTIÓN DE ESTADO (LECTURA)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Marca una notificación individual como leída vía AJAX.
     */
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

    /**
     * Marca todas las notificaciones pendientes del usuario como leídas.
     */
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
