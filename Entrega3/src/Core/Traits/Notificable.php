<?php

namespace Paw\Core\Traits;

use Paw\App\Models\Notificacion;
use Paw\App\Models\NotificacionCollection;

trait Notificable
{
    /**
     * Crea, guarda y emite una notificación en tiempo real.
     * Requiere que la clase que use este trait tenga acceso a $this->queryBuilder.
     *
     * @param int $usuarioId
     * @param string $titulo
     * @param string $mensaje
     * @param string $enlace
     */
    protected function notificar(int $usuarioId, string $titulo, string $mensaje, string $enlace): void
    {
        try {
            $redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
            $redisPort = getenv('REDIS_PORT') ?: '6379';
            $client = new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => $redisHost,
                'port'   => $redisPort,
            ]);

            $jobPayload = json_encode([
                'tipo' => 'notificacion',
                'datos' => [
                    'usuario_id' => $usuarioId,
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'enlace' => $enlace
                ]
            ]);

            $client->rpush('paw_jobs_queue', $jobPayload);
        } catch (\Exception $e) {
            // Fallback log
            error_log("No se pudo encolar la notificación en Redis: " . $e->getMessage());
        }
    }
}
