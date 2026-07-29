<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Paw\App\WebSockets\NotificationSocket;
use React\EventLoop\Loop;
use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(dirname(__DIR__));
// Ocultar warnings de deprecación (Ratchet usa código antiguo no 100% compatible con PHP 8.2+)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
$dotenv->safeLoad();

$loop = Loop::get();
$notificationSocket = new NotificationSocket();

// Configurar Redis pub/sub usando Clue\React\Redis
$factory = new Clue\React\Redis\Factory($loop);

$redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
$redisPort = getenv('REDIS_PORT') ?: '6379';
$redisUri = $redisHost . ':' . $redisPort;

$factory->createClient($redisUri)->then(function (Clue\React\Redis\Client $client) use ($notificationSocket) {
    $client->subscribe('notificaciones');
    
    $client->on('message', function ($canal, $payload) use ($notificationSocket) {
        echo "Mensaje recibido en el canal $canal: $payload\n";
        $datos = json_decode($payload, true);
        if ($datos && isset($datos['usuario_id'])) {
            $notificationSocket->notificarUsuario((int)$datos['usuario_id'], $payload);
        }
    });
}, function (Exception $e) {
    echo "No se pudo conectar a Redis: " . $e->getMessage() . "\n";
});

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $notificationSocket
        )
    ),
    8081,
    '0.0.0.0'
);

echo "Servidor WebSocket ejecutándose en el puerto 8081...\n";
$server->run();
