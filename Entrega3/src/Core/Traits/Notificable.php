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
        if (!property_exists($this, 'queryBuilder') || !$this->queryBuilder) {
            throw new \Exception("El trait Notificable requiere que la clase tenga la propiedad queryBuilder inicializada.");
        }

        $notificacion = new Notificacion();
        $notificacion->set([
            'usuario_id' => $usuarioId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'enlace' => $enlace
        ]);
        
        $notifCollection = new NotificacionCollection();
        $notifCollection->setQueryBuilder($this->queryBuilder);
        $notifCollection->agregarNotificacion($notificacion);
        $notifCollection->enviarNotificacionTiempoReal($notificacion);
    }
}
