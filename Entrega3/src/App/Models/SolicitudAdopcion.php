<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class SolicitudAdopcion extends Model
{
    public $table = 'solicitud_de_adopcion';

    public $fields = [
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
        $this->fields['nombre'] = $datos['nombre'] ?? null;
        $this->fields['apellido'] = $datos['apellido'] ?? null;
        $this->fields['email'] = $datos['email'] ?? null;
        $this->fields['telefono'] = $datos['telefono'] ?? null;
        $this->fields['fecha_nacimiento'] = $datos['fecha_nacimiento'] ?? null;
        $this->fields['mascota_id'] = $datos['mascota_id'] ?? null;
        $this->fields['acepta_contrato'] = isset($datos['acepta_contrato']) && $datos['acepta_contrato'] === 'on';
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

        return $errores;
    }

    public function guardar(int $refugio_id)
    {
        $data = [
            'mascota_id' => $this->fields['mascota_id'],
            'refugio_id' => $refugio_id,
            'fecha' => date('Y-m-d H:i:s'),
            'estado' => 'PENDIENTE',
            'contrato_aceptado' => 1,
            'fecha_aceptacion' => date('Y-m-d H:i:s')
        ];

        // Also save adopter details that are mapped
        $this->queryBuilder->insert($this->table, $data);
    }
}
