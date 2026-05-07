<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Refugio extends Model
{
    public $table = 'refugio';
    public $fields = [
        'id' => null,
        'usuario_id' => null,
        'ubicacion_id' => null,
        'nombre_institucion' => '',
        'cuit' => '',
        'cvu' => null,
        'alias' => null,
        'imagen' => 'default-refugio.jpg',
        'telefono' => '',
        'ciudad' => null,
        'provincia' => null,
        'adoptables_disponibles' => 0,
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
            throw new \Exception("El ID del refugio debe ser un entero mayor a 0");
        }

        $sql = "SELECT * FROM refugio WHERE usuario_id = :id";
        
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($record) {
            $this->set($record);
        } else {
            throw new \Exception("No se encontró un refugio con el ID proporcionado");
        }
    }
}
