<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\ModelNotFoundException;
use Exception;
use Paw\Core\Traits\Notificable;

class SolicitudAdopcionCollection extends Model
{
    use Notificable;
    public string $table = 'solicitud_de_adopcion';

    public function getSolicitudesAdoptante(int $adoptanteId): array
    {
        return $this->queryBuilder->obtenerSolicitudesPorAdoptante($this->table, $adoptanteId);
    }

    public function getAdopcionesAdoptante(int $adoptanteId): array
    {
        return $this->queryBuilder->obtenerAdopcionesPorAdoptante($this->table, $adoptanteId);
    }

    public function getById(int $id)
    {
        return $this->queryBuilder->selectOne($this->table, ['id' => $id]);
    }

    public function getSolicitudesRefugio(int $refugioId): array
    {
        return $this->queryBuilder->obtenerSolicitudesPorRefugio($this->table, $refugioId);
    }

    public function existeSolicitud(int $adoptanteId, int $mascotaId): bool
    {
        return $this->queryBuilder->exists($this->table, [
            'adoptante_id' => $adoptanteId,
            'mascota_id'   => $mascotaId,
        ]);
    }

    public function guardar(SolicitudAdopcion $solicitud, int $refugio_id)
    {
        $data = [
            'adoptante_id' => $solicitud->fields['adoptante_id'],
            'mascota_id' => $solicitud->fields['mascota_id'],
            'refugio_id' => $refugio_id,
            'fecha' => date('Y-m-d H:i:s'),
            'estado' => 'PENDIENTE',
            'contrato_aceptado' => 1,
            'fecha_aceptacion' => date('Y-m-d H:i:s')
        ];

        $this->queryBuilder->insert($this->table, $data);
        
        // Logica de notificaciones (Nueva Solicitud)
        $mascotaData = $this->queryBuilder->selectOne('mascota', ['id' => $solicitud->fields['mascota_id']]);
        if ($mascotaData) {
            $this->notificar(
                $refugio_id,
                "Nueva Solicitud de Adopción",
                "Han enviado una solicitud para adoptar a " . $mascotaData['nombre'],
                '/perfil'
            );
        }
    }

    public function procesarSolicitud(int $solicitudId, string $accion, int $refugioId): string
    {
        if ($accion !== 'aceptar' && $accion !== 'rechazar') {
            throw new Exception('Acción no válida. Solo se permite aceptar o rechazar.', 400);
        }

        $solicitud = $this->queryBuilder->selectOne($this->table, ['id' => $solicitudId]);

        if (!$solicitud) {
            throw new ModelNotFoundException('No se encontró la solicitud especificada.');
        }

        if ((int)$solicitud['refugio_id'] !== $refugioId) {
            throw new Exception('No tiene permisos para modificar esta solicitud.', 403);
        }

        if (strtoupper($solicitud['estado'] ?? '') !== 'PENDIENTE') {
            throw new Exception('La solicitud ya ha sido procesada previamente.', 400);
        }

        $nuevoEstado = ($accion === 'aceptar') ? 'APROBADA' : 'RECHAZADA';
        $mascotaId = ($accion === 'aceptar') ? (int)$solicitud['mascota_id'] : null;

        try {
            $this->queryBuilder->procesarSolicitudAdopcion($this->table, $solicitudId, $nuevoEstado, $mascotaId, date('Y-m-d H:i:s'));
            
            // Logica de notificaciones
            $adoptanteId = (int)$solicitud['adoptante_id'];
            
            $mascotaData = $this->queryBuilder->selectOne('mascota', ['id' => (int)$solicitud['mascota_id']]);
            $nombreMascota = $mascotaData ? $mascotaData['nombre'] : 'la mascota';
            
            $titulo = "Actualización de Solicitud";
            $estadoParseado = ($nuevoEstado === 'APROBADA') ? 'Aprobada' : 'Rechazada';
            $mensaje = "Tu solicitud por $nombreMascota ha sido $estadoParseado.";
                
            $this->notificar($adoptanteId, $titulo, $mensaje, '/perfil');

            // Notificar a usuarios que tienen a la mascota en favoritos
            if ($nuevoEstado === 'APROBADA' && $mascotaId !== null) {
                $favoritos = $this->queryBuilder->select('favorito', ['mascota_id' => $mascotaId]);
                foreach ($favoritos as $fav) {
                    $favAdoptanteId = (int)$fav['adoptante_id'];
                    // No notificar al adoptante que acaba de adoptar a la mascota
                    if ($favAdoptanteId === $adoptanteId) {
                        continue;
                    }
                    
                    $this->notificar(
                        $favAdoptanteId,
                        "¡Final Feliz!",
                        "La mascota $nombreMascota que estaba en tus favoritos por fin encontró un hogar.",
                        '/perfil'
                    );
                }
            }

        } catch (Exception $e) {
            throw new Exception('Error al actualizar la base de datos: ' . $e->getMessage(), 500);
        }

        return $nuevoEstado;
    }
}
