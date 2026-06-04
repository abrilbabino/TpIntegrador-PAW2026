<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Refugio extends Model
{
    public $table = 'refugio';
    public $fields = [
        'usuario_id' => null,
        'ubicacion_id' => null,
        'nombre_institucion' => '',
        'cuit' => '',
        'cvu' => null,
        'alias' => null,
        'imagen' => 'default-refugio.jpg',
        'telefono' => '',
        'email' => null,
        'ciudad' => null,
        'provincia' => null,
        'descripcion' => null,
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

        $sql = "SELECT r.*, u.ciudad, u.provincia 
                FROM refugio r 
                LEFT JOIN ubicacion u ON r.usuario_id = u.refugio_id 
                WHERE r.usuario_id = :id";
        
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

    public function getId(): int
    {
        return (int) $this->fields['usuario_id'];
    }

    public function getNombre(): string
    {
        return $this->fields['nombre_institucion'];
    }

    public function getAlias(): ?string
    {
        return $this->fields['alias'];
    }

    public function getDescripcion(): ?string
    {
        return $this->fields['descripcion'] ?? 'Este refugio aún no tiene una descripción detallada, pero trabaja arduamente día a día para rescatar y cuidar a los animales que más lo necesitan.';
    }

    public function getCvu(): ?string
    {
        return $this->fields['cvu'];
    }

    public function getEmail(): ?string
    {
        return $this->fields['email'];
    }
}
