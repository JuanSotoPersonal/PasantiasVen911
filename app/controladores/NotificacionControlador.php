<?php
/**
 * CONTROLADOR: NotificacionControlador
 * Propósito: Gestionar el estado de lectura de las alertas del sistema en base de datos
 * y actuar como Emisor (Publisher) hacia el Demonio de WebSockets (Ratchet) 
 * para la transmisión de notificaciones de alta eficiencia y latencia cero.
 */

require_once 'app/modelos/NotificacionModelo.php';
require_once 'app/Helpers/Notificador.php';

use App\modelos\NotificacionModelo;
use App\Helpers\Notificador;

class NotificacionControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    /**
     * @var NotificacionModelo Instancia del modelo para persistencia de datos.
     */
    private NotificacionModelo $modelo;

    /**
     * Constructor: Inicializa el modelo y garantiza la seguridad mediante
     * la validación estricta de la sesión activa del usuario.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        $this->modelo = new NotificacionModelo();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. GESTIÓN DE ESTADO (PERSISTENCIA Y LECTURA)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Recupera las notificaciones no leídas del usuario en sesión.
     * Utilizado para la carga inicial del frontend al abrir el sistema.
     */
    public function obtenerPendientes(): void {
        header('Content-Type: application/json');
        session_write_close();
        $usuario_id = (int)$_SESSION['user_id'];
        $notificaciones = $this->modelo->obtenerNoLeidas($usuario_id);
        echo json_encode(['success' => true, 'data' => $notificaciones]);
    }

    /**
     * Marca una notificación específica como leída de manera asíncrona (AJAX).
     * Devuelve un JSON neutro en caso de fallo para fallar con elegancia.
     */
    public function marcarLeida(): void {
        header('Content-Type: application/json');

        // Protección contra métodos HTTP no autorizados
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            return;
        }

        // Sanitización y casteo de variables de entrada
        $id_notif   = (int)($_POST['id'] ?? 0);
        $usuario_id = (int)$_SESSION['user_id'];

        if (!$id_notif) {
            echo json_encode(['success' => false, 'message' => 'ID de notificación inválido.']);
            return;
        }

        // Delegación de la actualización al modelo
        $resultado = $this->modelo->marcarLeida($id_notif, $usuario_id);
        echo json_encode(['success' => $resultado]);
    }

    /**
     * Acción masiva: Marca todas las notificaciones pendientes del 
     * usuario en sesión como leídas en una sola transacción.
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

    // ///////////////////////////////////////////////////////////////////
    // 3. CAPA DE EMISIÓN (RATCHET / REACTPHP)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Emisor de Alertas: Construye la carga útil (payload) y hace un pálpito HTTP
     * al puerto ciego del Demonio WebSocket para retransmitir la alerta en tiempo real.
     * 
     * @Nota: Actualmente funciona como endpoint temporal de PoC. En producción,
     * esta lógica se desacoplará y será invocada desde FichaControlador::guardar().
     */
    public function emitirPrueba(): void {
        $usuario_id = (int)$_SESSION['user_id'];
        
        \App\Helpers\Notificador::enviarAUsuario(
            $usuario_id,
            'alerta',
            'Prueba de Conexión',
            '¡Excelente! El sistema de WebSockets está funcionando y filtrando correctamente para tu usuario.',
            null
        );

        echo "Pálpito enviado vía Notificador Helper. Revisa tu panel de notificaciones.";
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. ESTADO DEL DEMONIO WEBSOCKET (SOLO ADMINISTRADOR)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Verifica si el servidor WebSocket (puerto 8081) está activo.
     * Usa fsockopen con timeout para no bloquear la interfaz.
     * Solo accesible para el Administrador (Rol 1).
     */
    public function estadoServidor(): void {
        header('Content-Type: application/json');
        session_write_close();

        // Restricción estricta: solo el Administrador puede consultar esto
        if ((int)$_SESSION['user_rol_id'] !== 1) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos.']);
            return;
        }

        $inicio = microtime(true);
        $conexion = @fsockopen('127.0.0.1', 8081, $errno, $errstr, 1);
        $latencia = round((microtime(true) - $inicio) * 1000); // ms

        if ($conexion) {
            fclose($conexion);
            echo json_encode([
                'activo'     => true,
                'latencia_ms' => $latencia,
                'mensaje'    => 'Servidor WebSocket operativo.',
            ]);
        } else {
            echo json_encode([
                'activo'     => false,
                'latencia_ms' => null,
                'mensaje'    => 'Demonio WebSocket no detectado en el puerto 8081.',
            ]);
        }
    }

    /**
     * Ejecuta el script de arranque del servidor WebSocket.
     * Solo accesible para el Administrador (Rol 1).
     * 
     * @Nota: Usa popen para lanzar el proceso en segundo plano sin bloquear PHP.
     */
    public function iniciarServidor(): void {
        header('Content-Type: application/json');

        // 1. SEGURIDAD: Solo Administrador (Rol 1)
        if ((int)($_SESSION['user_rol_id'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            return;
        }

        try {
            // 2. RUTA DEL SCRIPT
            $rutaBat = realpath('iniciar_ws.bat');
            
            if (!$rutaBat || !file_exists($rutaBat)) {
                echo json_encode(['success' => false, 'message' => 'Script de inicio no encontrado en la raíz.']);
                return;
            }

            // 3. EJECUCIÓN ASÍNCRONA (Windows)
            // Se usa 'start /B' para ejecutarlo minimizado y sin bloquear la respuesta HTTP
            pclose(popen("start /B \"\" \"$rutaBat\"", "r"));

            echo json_encode([
                'success' => true, 
                'message' => 'Comando de inicio enviado correctamente al sistema.'
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
