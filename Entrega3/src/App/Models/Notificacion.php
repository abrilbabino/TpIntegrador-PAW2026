<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Notificacion extends Model
{
    public $table = 'notificacion';

    public $fields = [
        'usuario_id' => null,
        'titulo' => null,
        'mensaje' => null,
        'enlace' => null,
        'leida' => false,
        'fecha_creacion' => null
    ];

    public function set(array $datos)
    {
        $this->fields['usuario_id'] = $datos['usuario_id'] ?? null;
        $this->fields['titulo'] = $datos['titulo'] ?? null;
        $this->fields['mensaje'] = $datos['mensaje'] ?? null;
        $this->fields['enlace'] = $datos['enlace'] ?? null;
        $this->fields['leida'] = isset($datos['leida']) ? (bool)$datos['leida'] : false;
        $this->fields['fecha_creacion'] = $datos['fecha_creacion'] ?? date('Y-m-d H:i:s');
    }

    public function validar()
    {
        $errores = [];

        if (empty($this->fields['usuario_id'])) {
            $errores['usuario_id'] = 'El usuario_id es obligatorio.';
        }
        if (empty($this->fields['titulo'])) {
            $errores['titulo'] = 'El título es obligatorio.';
        }
        if (empty($this->fields['mensaje'])) {
            $errores['mensaje'] = 'El mensaje no puede estar vacío.';
        }

        return $errores;
    }
}
