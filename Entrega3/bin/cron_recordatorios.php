<?php

require __DIR__ . '/../vendor/autoload.php';

use Paw\Core\Config;
use Paw\Core\Database\ConnectionBuilder;
use Paw\Core\Database\QueryBuilder;
use Paw\Core\MailService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
$config = new Config;

$log = new Logger('cron-recordatorios');
$handler = new StreamHandler($config->get("LOG_PATH"));
$handler->setLevel($config->get("LOG_LEVEL", "DEBUG"));
$log->pushHandler($handler);

$connectionBuilder = new ConnectionBuilder;
$connectionBuilder->setLogger($log);
$connection = $connectionBuilder->make($config);

$qb = new QueryBuilder($connection, $log);

// Consulta para PostgreSQL
try {
    echo "[Cron] Buscando registros sanitarios próximos a vencer...\n";
    $registros = $qb->obtenerRegistrosPendientesCercanos();
    
    if (empty($registros)) {
        echo "[Cron] No hay registros para notificar hoy.\n";
    }

    foreach ($registros as $row) {
        $fechaFormateada = date('d/m/Y', strtotime($row['fecha_programada']));
        $subject = "Recordatorio: Próximo turno de " . $row['mascota_nombre'];
        $body = "Hola {$row['adoptante_nombre']},\n\nTe recordamos que el próximo turno de {$row['mascota_nombre']} para '{$row['titulo']}' está programado para el {$fechaFormateada}.\n\nPor favor, ingresa a tu perfil para subir el comprobante una vez realizado.\n\nSaludos,\nEl equipo de PawMap.";

        $mailService = new MailService();
        $mailService->send($row['adoptante_email'], $subject, $body);

        // Actualizar registro
        $qb->marcarRegistroNotificado((int)$row['registro_id']);

        $log->info("Recordatorio simulado enviado a {$row['adoptante_email']} para el registro {$row['registro_id']}");
        echo "[Cron] Correo enviado a: {$row['adoptante_email']} (Mascota: {$row['mascota_nombre']})\n";
    }
    
    echo "[Cron] Proceso finalizado con éxito.\n";
} catch (\Exception $e) {
    $log->error("Error ejecutando cron_recordatorios.php: " . $e->getMessage());
    echo "[Cron] ERROR: " . $e->getMessage() . "\n";
}
