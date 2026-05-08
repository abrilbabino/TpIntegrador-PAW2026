<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Paw\Core\Exceptions\MascotaNotFoundException;

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
        'imagen' => 'default-pet.jpg',
        'sexo' => 'Desconocido',
        'fecha_adopcion' => null,
    ];

    public function setId($id){
        if(!is_numeric($id) || $id < 0){
            throw new InvalidValueFormatException("El ID de la mascota debe ser un entero mayor a 0");
        }
        $this->fields['id'] = $id;
    }

    public function setNombre(string $nombre){
        $this->fields['nombre'] = $nombre;
    }

    public function setImagen(string $imagen){
        $this->fields['imagen'] = $imagen ?? 'default-pet.jpg';
    }

    public function set(array $values)
    {
        foreach (array_keys($this->fields) as $field) {
            if (!isset($values[$field])) {
                continue;
            }
            $method = "set" . ucfirst($field);
            if (method_exists($this, $method)) {
                $this->$method($values[$field]);
            } else {
                $this->fields[$field] = $values[$field];
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
            throw new MascotaNotFoundException("No se encontró una mascota con el ID proporcionado");
        }
    }
}
