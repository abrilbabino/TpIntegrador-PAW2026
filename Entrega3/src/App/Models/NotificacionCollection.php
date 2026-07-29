<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class NotificacionCollection extends Model
{
    public string $table = 'notificacion';

    public function getNoLeidas(int $usuarioId): array
    {
        return $this->queryBuilder->obtenerNotificacionesNoLeidas($this->table, $usuarioId);
    }

    public function getRecientes(int $usuarioId, int $limit = 20): array
    {
        return $this->queryBuilder->obtenerNotificacionesRecientes($this->table, $usuarioId, $limit);
    }

    public function marcarComoLeidas(array $ids, int $usuarioId): void
    {
        foreach ($ids as $id) {
            // Add a check to ensure the notification belongs to the user
            $this->queryBuilder->update($this->table, ['leida' => true], [
                'id' => (int)$id,
                'usuario_id' => $usuarioId
            ]);
        }
    }

    public function agregarNotificacion(Notificacion $notificacion): bool
    {
        $errores = $notificacion->validar();
        if (!empty($errores)) {
            throw new \Exception(implode(', ', $errores));
        }

        $id = $this->queryBuilder->insert($this->table, $notificacion->fields);
        $notificacion->fields['id'] = $id;
        return (bool)$id;
    }

    public function enviarNotificacionTiempoReal(Notificacion $notificacion): void
    {
        try {
            $redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
            $redisPort = getenv('REDIS_PORT') ?: '6379';
            $client = new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => $redisHost,
                'port'   => $redisPort,
            ]);
            
            $payload = json_encode([
                'id' => $notificacion->fields['id'] ?? null,
                'usuario_id' => $notificacion->fields['usuario_id'],
                'titulo' => $notificacion->fields['titulo'],
                'mensaje' => $notificacion->fields['mensaje'],
                'enlace' => $notificacion->fields['enlace'],
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);
            
            $client->publish('notificaciones', $payload);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error("Error publicando notificación en Redis: " . $e->getMessage());
            }
        }
    }
}
