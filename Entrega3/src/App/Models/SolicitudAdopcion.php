<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class SolicitudAdopcion extends Model
{
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

        // Verificar solicitud duplicada contra la base de datos
        if (!empty($this->fields['adoptante_id']) && !empty($this->fields['mascota_id'])) {
            if ($this->existeSolicitud((int)$this->fields['adoptante_id'], (int)$this->fields['mascota_id'])) {
                $errores['solicitud_duplicada'] = 'Ya enviaste una solicitud de adopción para esta mascota. No es posible enviar más de una solicitud por mascota.';
            }
        }

        return $errores;
    }

    /**
     * Verifica si ya existe una solicitud de adopción del adoptante para la mascota indicada.
     */
    public function existeSolicitud(int $adoptanteId, int $mascotaId): bool
    {
        return $this->queryBuilder->exists($this->table, [
            'adoptante_id' => $adoptanteId,
            'mascota_id'   => $mascotaId,
        ]);
    }

    public function guardar(int $refugio_id)
    {
        $data = [
            'adoptante_id' => $this->fields['adoptante_id'],
            'mascota_id' => $this->fields['mascota_id'],
            'refugio_id' => $refugio_id,
            'fecha' => date('Y-m-d H:i:s'),
            'estado' => 'PENDIENTE',
            'contrato_aceptado' => 1,
            'fecha_aceptacion' => date('Y-m-d H:i:s')
        ];

        $this->queryBuilder->insert($this->table, $data);
    }
}
