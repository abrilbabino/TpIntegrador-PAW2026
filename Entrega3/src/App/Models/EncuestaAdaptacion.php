<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class EncuestaAdaptacion extends Model
{
    public $table = 'encuesta_adopcion';
    public $fields = [
        'id' => null,
        'mascota_id' => null,
        'adoptante_id' => null,
        'fecha_encuesta' => null,
        'conducta' => null,
        'sueno' => null,
        'alimentacion' => null,
        'progreso_general' => null,
        'comentarios' => null,
        'etapa' => 'inicial',
        'alerta_generada' => false
    ];

    public function set(array $values)
    {
        foreach (array_keys($this->fields) as $field) {
            if (!isset($values[$field])) {
                continue;
            }
            $this->fields[$field] = $values[$field];
        }
    }

    public function evaluarAlerta(): bool
    {
        $alerta = false;
        
        $conductaNegativa = in_array(strtolower($this->fields['conducta'] ?? ''), ['problemática', 'agresiva', 'miedosa']);
        $suenoNegativo = in_array(strtolower($this->fields['sueno'] ?? ''), ['intermitente', 'no duerme', 'llora']);
        $alimentacionNegativa = in_array(strtolower($this->fields['alimentacion'] ?? ''), ['falta de apetito', 'no come', 'vomita']);

        if ($conductaNegativa || $suenoNegativo || $alimentacionNegativa) {
            $alerta = true;
        }

        $this->fields['alerta_generada'] = $alerta;
        return $alerta;
    }
}
