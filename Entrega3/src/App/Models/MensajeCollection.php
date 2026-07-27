<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class MensajeCollection extends Model
{
    public $modelName = Mensaje::class;

    public function getMensajesPorSolicitud(int $solicitudId): array
    {
        $mensajesDB = $this->queryBuilder->obtenerMensajesPorSolicitud($solicitudId);

        $mensajes = [];
        foreach ($mensajesDB as $row) {
            $mensaje = new $this->modelName();
            $mensaje->set($row);
            // Inyectamos campos extra para la vista
            $mensaje->fields['id'] = $row['id'];
            $mensaje->fields['remitente_nombre'] = $row['remitente_nombre'];
            $mensaje->fields['remitente_rol'] = $row['remitente_rol'];
            $mensajes[] = $mensaje;
        }

        return $mensajes;
    }

    public function marcarComoLeidos(int $solicitudId, int $usuarioId)
    {
        $qb = clone $this->queryBuilder;
        $qb->update('mensaje', ['leido' => 1], [
            'solicitud_id' => $solicitudId,
            'destinatario_id' => $usuarioId,
            'leido' => 0
        ]);
    }

    public function getUnreadCount(int $usuarioId): int
    {
        return $this->queryBuilder->contarMensajesNoLeidos($usuarioId);
    }

    public function getUnreadCountBySolicitud(int $solicitudId, int $usuarioId): int
    {
        return $this->queryBuilder->contarMensajesNoLeidosPorSolicitud($solicitudId, $usuarioId);
    }

    public function guardar(Mensaje $mensaje)
    {
        $errores = $mensaje->validar();
        if (!empty($errores)) {
            return $errores;
        }

        $data = [
            'solicitud_id' => $mensaje->fields['solicitud_id'],
            'remitente_id' => $mensaje->fields['remitente_id'],
            'destinatario_id' => $mensaje->fields['destinatario_id'],
            'contenido' => $mensaje->fields['contenido'],
            'fecha_envio' => date('Y-m-d H:i:s'),
            'leido' => 0
        ];

        $qb = clone $this->queryBuilder;
        $qb->insert('mensaje', $data);
        return true;
    }
}
