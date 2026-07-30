<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\InvalidValueFormatException;

class ResenaCollection extends Model
{
    public string $table = 'resena';

    public function getResenasDestacadas(int $limite = 5): array
    {
        return $this->queryBuilder->obtenerResenasDestacadas($limite);
    }

    public function getAdopcionesSinResena(int $adoptanteId): array
    {
        return $this->queryBuilder->obtenerAdopcionesSinResena($adoptanteId);
    }

    public function guardarResena(Resena $resena): void
    {
        $errores = $resena->validar();
        if (!empty($errores)) {
            throw new InvalidValueFormatException(implode(', ', $errores));
        }

        $existe = $this->queryBuilder->exists($this->table, [
            'adoptante_id' => $resena->fields['adoptante_id'],
            'mascota_id' => $resena->fields['mascota_id']
        ]);
        
        if ($existe) {
            throw new InvalidValueFormatException("Ya has dejado una reseña para esta mascota.");
        }

        $this->queryBuilder->insertarResena([
            'adoptante_id' => $resena->fields['adoptante_id'],
            'mascota_id' => $resena->fields['mascota_id'],
            'refugio_id' => $resena->fields['refugio_id'],
            'calificacion' => $resena->fields['calificacion'],
            'comentario' => $resena->fields['comentario']
        ]);
    }
}
