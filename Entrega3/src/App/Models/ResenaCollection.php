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

    public function actualizarResena(int $id, int $adoptanteId, int $calificacion, string $comentario): void
    {
        if ($calificacion < 1 || $calificacion > 5) {
            throw new InvalidValueFormatException("La calificación debe estar entre 1 y 5.");
        }
        if (strlen(trim($comentario)) < 10 || strlen(trim($comentario)) > 250) {
            throw new InvalidValueFormatException("El comentario debe tener entre 10 y 250 caracteres.");
        }

        $this->queryBuilder->update($this->table, [
            'calificacion' => $calificacion,
            'comentario' => trim($comentario)
        ], [
            'id' => $id,
            'adoptante_id' => $adoptanteId
        ]);
    }

    public function eliminarResena(int $id, int $adoptanteId): void
    {
        $this->queryBuilder->delete($this->table, [
            'id' => $id,
            'adoptante_id' => $adoptanteId
        ]);
    }
}
