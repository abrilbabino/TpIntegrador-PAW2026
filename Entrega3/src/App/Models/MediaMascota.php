<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class MediaMascota extends Model
{
    public $table = 'media_mascota';

    public $fields = [
        'id' => null,
        'mascota_id' => null,
        'tipo' => null,
        'url' => null,
    ];

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $this->fields)) {
                $this->fields[$key] = $value;
            }
        }
    }
}
