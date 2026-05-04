<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\InvalidValueFormatException;

class Mascota extends Model
{
    public $table = 'mascota';
    
    public $fields = [
        'id' => null,
        'refugio_id' => null,
        'nombre' => null,
        'especie' => null,
        'descripcion' => null,
        'edad' => null,
        'tamano' => null,
        'temperamento' => null,
        'estado_adopcion' => null,
        'vacunado' => null,
        'castrado' => null,
        'imagen' => 'default-pet.jpg'
    ];

    public function set(array $values)
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $this->fields)) {
                $this->fields[$key] = $value;
            }
        }
    }

    public function load($id){
        if(!is_numeric($id) || $id < 0){
            throw new InvalidValueFormatException("El ID de la mascota debe ser un entero mayor a 0");
        }
        $params = ['id' => $id];
        $record = current($this->queryBuilder->select($this->table, $params));
        if ($record) {
            $this->set($record);
        }
        else{
            throw new \Exception("No se encontró una mascota con el ID proporcionado");
        }
    }

    public static function getAllMascotas($queryBuilder)
    {
        $result = $queryBuilder->select('mascota', ['estado_adopcion' => 'DISPONIBLE']);
        $mascotas = [];
        foreach ($result as $row) {
            $mascota = new self();
            $mascota->set($row);
            $mascotas[] = $mascota;
        }
        return $mascotas;
    }
}