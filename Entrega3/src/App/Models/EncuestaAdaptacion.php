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
        $this->limpiarCamposPorEtapa();
    }

    private function limpiarCamposPorEtapa()
    {
        $etapa = $this->fields['etapa'];
        
        if ($etapa === '3_dias') {
            $this->fields['conducta'] = null;
            $this->fields['progreso_general'] = null;
        } elseif ($etapa === '7_dias') {
            $this->fields['sueno'] = null;
            $this->fields['alimentacion'] = null;
            $this->fields['progreso_general'] = null;
        } elseif ($etapa === '14_dias') {
            $this->fields['sueno'] = null;
            $this->fields['alimentacion'] = null;
            $this->fields['conducta'] = null;
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

    public function save(): void
    {
        $db = $this->getQueryBuilder();
        $db->insert($this->table, [
            'mascota_id' => $this->fields['mascota_id'],
            'adoptante_id' => $this->fields['adoptante_id'],
            'fecha_encuesta' => $this->fields['fecha_encuesta'],
            'etapa' => $this->fields['etapa'],
            'conducta' => $this->fields['conducta'],
            'sueno' => $this->fields['sueno'],
            'alimentacion' => $this->fields['alimentacion'],
            'progreso_general' => $this->fields['progreso_general'],
            'comentarios' => $this->fields['comentarios'],
            'alerta_generada' => $this->fields['alerta_generada'] ? 1 : 0
        ]);
    }
}
