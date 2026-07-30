<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Paw\Core\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Paw\Core\Database\ConnectionBuilder;
use Paw\Core\Database\QueryBuilder;
use Paw\App\Models\Notificacion;
use Paw\App\Models\NotificacionCollection;

date_default_timezone_set('America/Argentina/Buenos_Aires');

$dotenv = Dotenv::createUnsafeImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$config = new Config;

$log = new Logger('pawmap-worker');
$handler = new StreamHandler($config->get("LOG_PATH", dirname(__DIR__) . "/logs/app.log"));
$handler->setLevel($config->get("LOG_LEVEL", "DEBUG"));
$log->pushHandler($handler);

$connectionBuilder = new ConnectionBuilder;
$connectionBuilder->setLogger($log);
$connection = $connectionBuilder->make($config);
$queryBuilder = new QueryBuilder($connection, $log);

$redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
$redisPort = getenv('REDIS_PORT') ?: '6379';

$client = new \Predis\Client([
    'scheme' => 'tcp',
    'host'   => $redisHost,
    'port'   => $redisPort,
    'read_write_timeout' => 0
]);

echo "Iniciando worker de notificaciones...\n";
echo "Escuchando cola 'paw_jobs_queue'...\n";

while (true) {
    try {
        // BLPOP bloquea la ejecución hasta que haya un elemento en la cola
        $result = $client->blpop('paw_jobs_queue', 0);
        
        if ($result) {
            $queue = $result[0];
            $payload = $result[1];
            
            $job = json_decode($payload, true);
            
            if ($job && isset($job['tipo']) && $job['tipo'] === 'notificacion') {
                $datos = $job['datos'];
                echo "[" . date('Y-m-d H:i:s') . "] Procesando notificación para usuario ID: " . $datos['usuario_id'] . "\n";
                
                $notificacion = new Notificacion();
                $notificacion->set([
                    'usuario_id' => $datos['usuario_id'],
                    'titulo' => $datos['titulo'],
                    'mensaje' => $datos['mensaje'],
                    'enlace' => $datos['enlace']
                ]);
                
                $notifCollection = new NotificacionCollection();
                $notifCollection->setQueryBuilder($queryBuilder);
                $notifCollection->agregarNotificacion($notificacion);
                
                $client->publish('notificaciones', json_encode([
                    'id' => $notificacion->fields['id'] ?? null,
                    'usuario_id' => $datos['usuario_id'],
                    'titulo' => $datos['titulo'],
                    'mensaje' => $datos['mensaje'],
                    'enlace' => $datos['enlace'],
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]));
            }
        }
    } catch (\Exception $e) {
        $log->error("Error en el worker: " . $e->getMessage());
        echo "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
        sleep(2); // Pausa breve si se cae la conexión a DB o Redis
    }
}
