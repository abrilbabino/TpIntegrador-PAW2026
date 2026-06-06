<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Mensaje extends Model
{
    public $table = 'mensaje';

    public $fields = [
        'solicitud_id' => null,
        'remitente_id' => null,
        'destinatario_id' => null,
        'contenido' => null,
        'leido' => false,
        'fecha_envio' => null
    ];

    public function set(array $datos)
    {
        $this->fields['solicitud_id'] = $datos['solicitud_id'] ?? null;
        $this->fields['remitente_id'] = $datos['remitente_id'] ?? null;
        $this->fields['destinatario_id'] = $datos['destinatario_id'] ?? null;
        $this->fields['contenido'] = $datos['contenido'] ?? null;
        $this->fields['leido'] = isset($datos['leido']) ? (bool)$datos['leido'] : false;
        $this->fields['fecha_envio'] = $datos['fecha_envio'] ?? null;
    }

    public function validar()
    {
        $errores = [];

        if (empty($this->fields['solicitud_id'])) {
            $errores['solicitud_id'] = 'La solicitud_id es obligatoria.';
        }
        if (empty($this->fields['remitente_id'])) {
            $errores['remitente_id'] = 'El remitente es obligatorio.';
        }
        if (empty($this->fields['destinatario_id'])) {
            $errores['destinatario_id'] = 'El destinatario es obligatorio.';
        }
        if (empty($this->fields['contenido']) || trim($this->fields['contenido']) === '') {
            $errores['contenido'] = 'El mensaje no puede estar vacío.';
        }

        return $errores;
    }
}
