<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Adoptante extends Model
{
    public $table = 'adoptante';
    public $fields = [
        'usuario_id' => null,
        'ubicacion_id' => null,
        'nombre' => '',
        'apellido' => '',
        'dni' => '',
        'fecha_de_nacimiento' => null,
        // Agrega aquí otros campos que tengas en tu tabla 'adoptante'
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

    public function load($id)
    {
        if (!is_numeric($id) || $id < 0) {
            throw new \Exception("El ID del adoptante debe ser un entero mayor a 0");
        }

        $sql = "SELECT * FROM adoptante WHERE usuario_id = :id";
        
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($record) {
            $this->set($record);
        } else {
            throw new \Exception("No se encontró un adoptante con el ID proporcionado");
        }
    }
}
