<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class MensajeCollection extends Model
{
    public $modelName = Mensaje::class;

    public function getMensajesPorSolicitud(int $solicitudId): array
    {
        $qb = clone $this->queryBuilder;
        $sql = "SELECT m.*, r.nombre_usuario as remitente_nombre, r.rol as remitente_rol 
                FROM mensaje m 
                JOIN usuario r ON m.remitente_id = r.id 
                WHERE m.solicitud_id = :solicitud_id 
                ORDER BY m.fecha_envio ASC";
        $mensajesDB = $qb->rawQuery($sql, [':solicitud_id' => $solicitudId]);

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
        $qb = clone $this->queryBuilder;
        $sql = "SELECT COUNT(*) as count FROM mensaje WHERE destinatario_id = :destinatario_id AND leido = false";
        $count = $qb->rawQueryValue($sql, [':destinatario_id' => $usuarioId]);

        return (int)$count;
    }

    public function getUnreadCountBySolicitud(int $solicitudId, int $usuarioId): int
    {
        $qb = clone $this->queryBuilder;
        $sql = "SELECT COUNT(*) as count FROM mensaje WHERE solicitud_id = :solicitud_id AND destinatario_id = :destinatario_id AND leido = false";
        $count = $qb->rawQueryValue($sql, [
            ':solicitud_id' => $solicitudId,
            ':destinatario_id' => $usuarioId
        ]);

        return (int)$count;
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
