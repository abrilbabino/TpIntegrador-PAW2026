<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Traits\Notificable;

class EncuestaAdaptacionCollection extends Model
{
    use Notificable;
    public string $table = 'encuesta_adopcion';

    public function guardar(EncuestaAdaptacion $encuesta): void
    {
        $db = $this->getQueryBuilder();
        $db->insert($this->table, [
            'mascota_id' => $encuesta->fields['mascota_id'],
            'adoptante_id' => $encuesta->fields['adoptante_id'],
            'fecha_encuesta' => $encuesta->fields['fecha_encuesta'],
            'etapa' => $encuesta->fields['etapa'],
            'conducta' => $encuesta->fields['conducta'],
            'sueno' => $encuesta->fields['sueno'],
            'alimentacion' => $encuesta->fields['alimentacion'],
            'progreso_general' => $encuesta->fields['progreso_general'],
            'comentarios' => $encuesta->fields['comentarios'],
            'alerta_generada' => $encuesta->fields['alerta_generada'] ? 1 : 0
        ]);
        
        // Logica de notificaciones (Adoptante -> Refugio)
        $mascotaData = $db->selectOne('mascota', ['id' => (int)$encuesta->fields['mascota_id']]);
        if ($mascotaData) {
            $this->notificar(
                $mascotaData['refugio_id'],
                "Nueva Actualización de Seguimiento",
                "El adoptante respondió una encuesta de adaptación de " . $mascotaData['nombre'],
                '/perfil'
            );
        }
    }
}
