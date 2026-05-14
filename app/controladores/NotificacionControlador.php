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
    /**
     * Verifica el estado de los servicios de notificación.
     * 1. Puerto 8081 (Servidor WebSocket Interno)
     * 2. RabbitMQ (Puerto 5672)
     * 3. Proceso del Worker en el Sistema Operativo
     */
    public function estadoServidor(): void {
        header('Content-Type: application/json');
        session_write_close();

        if ((int)$_SESSION['user_rol_id'] !== 1) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos.']);
            return;
        }

        // Obtener variables de entorno (Docker) con fallback a localhost (XAMPP local)
        $wsHost = getenv('WS_INTERNAL_HOST') ?: '127.0.0.1';
        $rabbitHost = getenv('RABBITMQ_HOST') ?: '127.0.0.1';
        $rabbitPort = getenv('RABBITMQ_PORT') ?: 5672;
        $rabbitUser = getenv('RABBITMQ_USER') ?: 'ven911';
        $rabbitPass = getenv('RABBITMQ_PASS') ?: 'ven911_mq_pass';
        $rabbitQueue = getenv('RABBITMQ_QUEUE') ?: 'notificaciones';

        // 1. Validar Servidor WebSocket (8081 Interno)
        $inicio = microtime(true);
        $fpWS = @fsockopen($wsHost, 8081, $errno, $errstr, 0.5);
        $latencia = round((microtime(true) - $inicio) * 1000);

        $wsActivo = false;
        if ($fpWS) {
            $wsActivo = true;
            fclose($fpWS);
        }

        // 2. Validar RabbitMQ (Puerto 5672)
        $fpRabbit = @fsockopen($rabbitHost, $rabbitPort, $errno, $errstr, 0.5);
        $rabbitActivo = false;
        if ($fpRabbit) {
            $rabbitActivo = true;
            fclose($fpRabbit);
        }

        // 3. Validar Proceso Worker PHP Activo
        $workerActivo = false;
        
        // Si estamos en entorno Docker (getenv devuelve algo), verificamos vía API de RabbitMQ
        if (getenv('RABBITMQ_HOST')) {
            // URL de la API de Management de RabbitMQ (Puerto 15672)
            $url = "http://{$rabbitUser}:{$rabbitPass}@{$rabbitHost}:15672/api/queues/%2F/{$rabbitQueue}";
            $ctx = stream_context_create(['http' => ['timeout' => 1]]);
            $res = @file_get_contents($url, false, $ctx);
            
            if ($res) {
                $data = json_decode($res, true);
                if (isset($data['consumers']) && $data['consumers'] > 0) {
                    $workerActivo = true;
                }
            }
        } else {
            // Entorno local Windows/XAMPP
            $out = [];
            $cmd = 'powershell -NoProfile -Command "if (Get-CimInstance Win32_Process -Filter \\"CommandLine LIKE \'%consumidor_notif.php%\'\\" -ErrorAction SilentlyContinue) { echo 1 } else { echo 0 }"';
            exec($cmd, $out);
            if (!empty($out) && trim($out[0]) === '1') {
                $workerActivo = true;
            }
        }

        echo json_encode([
            'success'      => true,
            'ws_activo'    => $wsActivo,
            'rabbit_activo'=> $rabbitActivo,
            'worker_activo'=> $workerActivo,
            'latencia_ms'  => $latencia,
            'mensaje'      => ($wsActivo && $rabbitActivo && $workerActivo) ? 'Servicios operativos.' : 'Uno o más servicios fuera de línea.'
        ]);
    }

    /**
     * Ejecuta el script de arranque de los demonios PHP directamente.
     * Sin depender de archivos .bat externos.
     */
    public function iniciarServidor(): void {
        header('Content-Type: application/json');

        if ((int)($_SESSION['user_rol_id'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            return;
        }

        // Si estamos en Docker, los contenedores se manejan solos
        if (getenv('WS_INTERNAL_HOST')) {
            echo json_encode(['success' => false, 'message' => 'Entorno Docker detectado. Los servicios se administran automáticamente mediante el contenedor.']);
            return;
        }

        try {
            $phpExe = 'C:\\xampp\\php\\php.exe';
            
            // Rutas absolutas a los scripts
            $wsScript     = realpath(__DIR__ . '/../bin/servidor_ws.php');
            $workerScript = realpath(__DIR__ . '/../bin/consumidor_notif.php');
            $logDir       = realpath(__DIR__ . '/../bin');

            if (!$wsScript || !$workerScript) {
                echo json_encode(['success' => false, 'message' => 'Scripts de inicio no encontrados.']);
                return;
            }

            $wsLog     = $logDir . '\\servidor_ws.log';
            $workerLog = $logDir . '\\worker.log';

            // Función anidada para lanzar proceso silencioso en PowerShell
            $lanzarEnBackground = function($script, $log) use ($phpExe) {
                // Se usa PowerShell Start-Process oculto para lanzar el demonio PHP sin bloquear ni mostrar ventana
                $psCommand = "Start-Process -FilePath '$phpExe' -ArgumentList '$script' -WindowStyle Hidden -RedirectStandardOutput '$log' -RedirectStandardError '$log'";
                $cmd = "powershell -NoProfile -WindowStyle Hidden -Command \"$psCommand\"";
                pclose(popen($cmd, "r"));
            };

            // 1. Evitar duplicar WS
            $fpWS = @fsockopen('127.0.0.1', 8080, $errno, $errstr, 0.5);
            if (!$fpWS) {
                $lanzarEnBackground($wsScript, $wsLog);
            } else {
                fclose($fpWS);
            }

            // 2. Evitar duplicar Worker
            $out = [];
            exec('powershell -NoProfile -Command "if (Get-CimInstance Win32_Process -Filter \\"CommandLine LIKE \'%consumidor_notif.php%\'\\" -ErrorAction SilentlyContinue) { echo 1 } else { echo 0 }"', $out);
            if (empty($out) || trim($out[0]) !== '1') {
                $lanzarEnBackground($workerScript, $workerLog);
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Servicios iniciados en segundo plano correctamente.'
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
