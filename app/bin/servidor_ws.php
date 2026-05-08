<?php
/**
 * servidor_ws.php - Demonio de Notificaciones (WebSockets + HTTP)
 *
 * Maneja dos puertos simultáneamente:
 * - 8080 (WebSockets): Escucha conexiones de navegadores y envía notificaciones en tiempo real.
 * - 8081 (HTTP):       Recibe pálpitos internos desde XAMPP/PHP para emitir notificaciones.
 *
 * OPTIMIZACIÓN: Los clientes se registran con su usuario_id al conectar.
 * El servidor enruta la notificación SOLO al cliente correcto, eliminando
 * el broadcast masivo previo (N envíos → 1 envío dirigido).
 */

// 1. Cargar autoloader de dependencias (generado en /vendor)
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\Socket\SocketServer;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\R
esponse;

// 2. Clase Pusher: Mantiene el mapa usuario_id → conexión para enrutamiento directo
class NotificadorPusher implements MessageComponentInterface {

    // Mapa [usuario_id => ConnectionInterface] para enrutamiento dirigido
    protected array $mapaUsuarios = [];

    // Conexiones sin registrar aún (antes de recibir su mensaje de identificación)
    protected $clientes;

    public function __construct() {
        $this->clientes = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clientes->attach($conn);
        echo "Nueva conexión WS! ({$conn->resourceId})\n";
    }

    /**
     * Procesa mensajes entrantes del navegador.
     * Protocolo: el cliente envía {"action":"registrar","usuario_id":123} al conectar.
     * Esto asocia la conexión con el usuario para el enrutamiento posterior.
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $datos = json_decode($msg, true);

        if (!is_array($datos)) {
            return; // Ignorar mensajes malformados
        }

        // Registro del cliente: asociar conexión con usuario_id
        if (($datos['action'] ?? '') === 'registrar' && isset($datos['usuario_id'])) {
            $usuarioId = (int)$datos['usuario_id'];
            $this->mapaUsuarios[$usuarioId] = $from;
            echo "Usuario {$usuarioId} registrado en WS (conn: {$from->resourceId})\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clientes->detach($conn);

        // Limpiar el mapa si el usuario registrado se desconecta
        foreach ($this->mapaUsuarios as $usuarioId => $conexion) {
            if ($conexion === $conn) {
                unset($this->mapaUsuarios[$usuarioId]);
                echo "Usuario {$usuarioId} eliminado del mapa WS.\n";
                break;
            }
        }

        echo "Conexión WS terminada ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error WS: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Emite un mensaje a los destinatarios indicados en el JSON.
     *
     * Si el payload incluye 'destinatarios' (array de usuario_ids), se enruta
     * a cada uno de ellos individualmente.
     * Si incluye un único 'usuario_id', se enruta solo a ese usuario.
     * Fallback: broadcast a todos (compatibilidad hacia atrás).
     */
    public function emitirAlerta(string $mensajeJSON): void {
        $datos = json_decode($mensajeJSON, true);

        // Caso A: múltiples destinatarios (batch desde enviarPorRol)
        if (isset($datos['destinatarios']) && is_array($datos['destinatarios'])) {
            $enviados = 0;
            foreach ($datos['destinatarios'] as $notif) {
                $uid = (int)($notif['usuario_id'] ?? 0);
                if ($uid && isset($this->mapaUsuarios[$uid])) {
                    // Enviamos el payload individual de esa notificación
                    $this->mapaUsuarios[$uid]->send(json_encode($notif));
                    $enviados++;
                }
            }
            echo "Batch enviado: {$enviados}/" . count($datos['destinatarios']) . " usuario(s) alcanzados.\n";
            return;
        }

        // Caso B: destinatario único (desde enviarAUsuario)
        if (isset($datos['usuario_id'])) {
            $uid = (int)$datos['usuario_id'];
            if (isset($this->mapaUsuarios[$uid])) {
                $this->mapaUsuarios[$uid]->send($mensajeJSON);
                echo "Notificación enviada al usuario {$uid}.\n";
            } else {
                echo "Usuario {$uid} no conectado. Notificación descartada del bus WS.\n";
            }
            return;
        }

        // Fallback: broadcast (no debería ocurrir en flujo normal)
        foreach ($this->clientes as $cliente) {
            $cliente->send($mensajeJSON);
        }
        echo "Broadcast a " . count($this->clientes) . " cliente(s) (fallback).\n";
    }
}

// 3. Configuración del Loop Asíncrono
$loop   = React\EventLoop\Loop::get();
$pusher = new NotificadorPusher();

// 4. Levantar Puerto 8080 (WebSockets para Navegadores)
$socketWeb = new SocketServer('0.0.0.0:8080', [], $loop);
$servidorWS = new IoServer(
    new HttpServer(
        new WsServer($pusher)
    ),
    $socketWeb,
    $loop
);

// 5. Levantar Puerto 8081 (Receptor HTTP Interno para Controladores PHP)
$socketHTTP = new SocketServer('0.0.0.0:8081', [], $loop);
$servidorHTTP = new React\Http\HttpServer(function (ServerRequestInterface $request) use ($pusher) {
    $cuerpo = (string)$request->getBody();
    echo "Recibido pálpito HTTP: $cuerpo\n";
    $pusher->emitirAlerta($cuerpo);
    return new Response(200, ['Content-Type' => 'text/plain'], "OK\n");
});
$servidorHTTP->listen($socketHTTP);

echo "--------------------------------------------------------\n";
echo "Servidor Pub/Sub iniciado correctamente.\n";
echo "-> WebSockets escuchando en el puerto 8080 (Público)\n";
echo "-> HTTP Listener escuchando en el puerto 8081 (Interno)\n";
echo "Esperando conexiones...\n";
echo "--------------------------------------------------------\n";

$loop->run();
