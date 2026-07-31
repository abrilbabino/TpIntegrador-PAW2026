<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Diccionario extends Model
{
    public $fields = [
        'id' => null,
        'nombre' => null
    ];

    public function setNombre(string $nombre)
    {
        $this->fields['nombre'] = $nombre;
    }

    public function getNombre(): ?string
    {
        return $this->fields['nombre'];
    }

    public function getId(): ?int
    {
        return $this->fields['id'];
    }
}
