<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\ModelNotFoundException;
use Paw\Core\Traits\Notificable;

class SolicitudAdopcion extends Model
{
    use Notificable;
    public $table = 'solicitud_de_adopcion';

    public $fields = [
        'adoptante_id' => null,
        'nombre' => null,
        'apellido' => null,
        'email' => null,
        'telefono' => null,
        'fecha_nacimiento' => null,
        'mascota_id' => null,
        'acepta_contrato' => false,
    ];

    public function set(array $datos)
    {
        $this->fields['adoptante_id'] = $datos['adoptante_id'] ?? null;
        $this->fields['nombre'] = $datos['nombre'] ?? null;
        $this->fields['apellido'] = $datos['apellido'] ?? null;
        $this->fields['email'] = $datos['email'] ?? null;
        $this->fields['telefono'] = $datos['telefono'] ?? null;
        $this->fields['fecha_nacimiento'] = $datos['fecha_nacimiento'] ?? null;
        $this->fields['mascota_id'] = $datos['mascota_id'] ?? null;
        $this->fields['acepta_contrato'] = isset($datos['acepta_contrato']) && ($datos['acepta_contrato'] === 'on' || $datos['acepta_contrato'] === true || $datos['acepta_contrato'] === 1);
    }

    public function validar()
    {
        $errores = [];

        if (empty($this->fields['nombre'])) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }
        if (empty($this->fields['apellido'])) {
            $errores['apellido'] = 'El apellido es obligatorio.';
        }
        if (!filter_var($this->fields['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato de email no es válido.';
        }
        if (empty($this->fields['telefono'])) {
            $errores['telefono'] = 'El teléfono es obligatorio.';
        }
        if (empty($this->fields['mascota_id'])) {
            $errores['mascota_id'] = 'La mascota no es válida.';
        }
        if (empty($this->fields['acepta_contrato'])) {
            $errores['acepta_contrato'] = 'Debe aceptar los términos del contrato de adopción y seguimiento sanitario.';
        }

        if (!empty($this->fields['fecha_nacimiento'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $this->fields['fecha_nacimiento']);
            if (!$d || $d->format('Y-m-d') !== $this->fields['fecha_nacimiento']) {
                $errores['fecha_nacimiento'] = 'Fecha de nacimiento inválida.';
            } elseif ($d > new \DateTime()) {
                $errores['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura.';
            }
        }

        return $errores;
    }


}
