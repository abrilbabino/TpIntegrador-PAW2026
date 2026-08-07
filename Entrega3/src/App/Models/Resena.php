<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Resena extends Model
{
    public $table = 'resena';

    public $fields = [
        'id' => null,
        'adoptante_id' => null,
        'mascota_id' => null,
        'refugio_id' => null,
        'calificacion' => null,
        'comentario' => null,
        'fecha_creacion' => null,
    ];

    public function set(array $datos)
    {
        $this->fields['id'] = $datos['id'] ?? null;
        $this->fields['adoptante_id'] = $datos['adoptante_id'] ?? null;
        $this->fields['mascota_id'] = $datos['mascota_id'] ?? null;
        $this->fields['refugio_id'] = $datos['refugio_id'] ?? null;
        $this->fields['calificacion'] = $datos['calificacion'] ?? null;
        $this->fields['comentario'] = $datos['comentario'] ?? null;
        $this->fields['fecha_creacion'] = $datos['fecha_creacion'] ?? date('Y-m-d H:i:s');
    }

    public function validar()
    {
        $errores = [];

        if (empty($this->fields['adoptante_id'])) {
            $errores['adoptante_id'] = "El ID del adoptante es requerido.";
        }
        if (empty($this->fields['mascota_id'])) {
            $errores['mascota_id'] = "El ID de la mascota es requerido.";
        }
        if (empty($this->fields['refugio_id'])) {
            $errores['refugio_id'] = "El ID del refugio es requerido.";
        }
        
        $calificacion = (int) $this->fields['calificacion'];
        if ($calificacion < 1 || $calificacion > 5) {
            $errores['calificacion'] = "La calificación debe ser entre 1 y 5 estrellas.";
        }

        $limpio = trim($this->fields['comentario'] ?? '');
        if (mb_strlen($limpio, 'UTF-8') < 10) {
            $errores['comentario'] = "El comentario debe tener al menos 10 caracteres.";
        } elseif (mb_strlen($limpio, 'UTF-8') > 250) {
            $errores['comentario'] = "El comentario no puede superar los 250 caracteres.";
        } else {
            $this->fields['comentario'] = $limpio;
        }

        return $errores;
    }
}
