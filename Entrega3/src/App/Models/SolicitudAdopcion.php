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
    ];

    public function set(array $datos)
    {
        $this->fields['nombre'] = $datos['nombre'] ?? null;
        $this->fields['apellido'] = $datos['apellido'] ?? null;
        $this->fields['email'] = $datos['email'] ?? null;
        $this->fields['telefono'] = $datos['telefono'] ?? null;
        $this->fields['fecha_nacimiento'] = $datos['fecha_nacimiento'] ?? null;
        $this->fields['mascota_id'] = $datos['mascota_id'] ?? null;
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

        return $errores;
    }

    public function guardar(int $refugio_id)
    {
        $stmt = $this->queryBuilder->getConnection()->prepare(
            "INSERT INTO {$this->table} (mascota_id, refugio_id, fecha, estado)
             VALUES (:mascota_id, :refugio_id, CURRENT_TIMESTAMP, 'PENDIENTE')"
        );
        $stmt->bindValue(':mascota_id', $this->fields['mascota_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':refugio_id', $refugio_id, \PDO::PARAM_INT);
        $stmt->execute();
    }
}
