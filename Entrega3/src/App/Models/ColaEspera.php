<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class ColaEspera extends Model
{
    public string $table = 'cola_espera';
    
    public array $fields = [
        'id' => null,
        'usuario_id' => null,
        'especie' => null,
        'raza' => null,
        'tamano' => null,
        'temperamento' => null,
        'provincia' => null,
        'ciudad' => null,
        'created_at' => null
    ];

    public function set(array $values): void
    {
        foreach ($this->fields as $field => $value) {
            if (isset($values[$field])) {
                $this->fields[$field] = $values[$field];
            }
        }
    }
}
