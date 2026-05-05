<?php
/**
 * servidor_ws.php - Demonio de Notificaciones (WebSockets + HTTP)
 * 
 * Este script corre en segundo plano y maneja dos puertos simultáneamente:
 * - 8080 (WebSockets): Escucha conexiones de navegadores y envía notificaciones en tiempo real.
 * - 8081 (HTTP): Recibe pálpitos internos desde XAMPP/PHP para emitir notificaciones.
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
use React\Http\Message\Response;

// 2. Clase Pusher: Mantiene a los clientes WS y distribuye los mensajes
class NotificadorPusher implements MessageComponentInterface {
    protected $clientes;

    public function __construct() {
        $this->clientes = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clientes->attach($conn);
        echo "Nueva conexión WS! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // En este diseño, los navegadores solo escuchan. No procesamos mensajes entrantes.
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clientes->detach($conn);
        echo "Conexión WS terminada ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error WS: {$e->getMessage()}\n";
        $conn->close();
    }

    // Método para disparar la alerta a todos los navegadores conectados
    public function emitirAlerta($mensajeJSON) {
        foreach ($this->clientes as $cliente) {
            $cliente->send($mensajeJSON);
        }
        echo "Notificación distribuida a " . count($this->clientes) . " cliente(s).\n";
    }
}

// 3. Configuración del Loop Asíncrono
$loop = React\EventLoop\Loop::get();
$pusher = new NotificadorPusher();

// 4. Levantar Puerto 8080 (WebSockets para Navegadores)
$socketWeb = new SocketServer('0.0.0.0:8080', [], $loop);
$servidorWS = new IoServer(
    new HttpServer(
        new WsServer(
            $pusher
        )
    ),
    $socketWeb,
    $loop
);

// 5. Levantar Puerto 8081 (Receptor HTTP Interno para Controladores PHP)
$socketHTTP = new SocketServer('127.0.0.1:8081', [], $loop);
$servidorHTTP = new React\Http\HttpServer(function (ServerRequestInterface $request) use ($pusher) {
    // Cuando el Controlador PHP mande la notificación, la retransmitimos
    $cuerpo = (string)$request->getBody();
    
    // Podríamos validar seguridad aquí (ej. verificar una llave secreta)
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
