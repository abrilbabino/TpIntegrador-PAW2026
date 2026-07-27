<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Favorito extends Model
{
    public string $table = 'favorito';

    /**
     * Agrega una mascota a favoritos (evita duplicados).
     * @return string|false El ID del nuevo favorito o false si ya existía.
     */
    public function agregar(int $adoptanteId, int $mascotaId): string|false
    {
        // Verificar si ya existe
        if ($this->queryBuilder->exists($this->table, [
            'adoptante_id' => $adoptanteId,
            'mascota_id'   => $mascotaId,
        ])) {
            return false;
        }

        return $this->queryBuilder->insert($this->table, [
            'adoptante_id' => $adoptanteId,
            'mascota_id'   => $mascotaId,
        ]);
    }

    /**
     * Obtiene todos los favoritos de un adoptante con datos de la mascota.
     */
    public function getByAdoptanteId(int $adoptanteId): array
    {
        return $this->queryBuilder->obtenerFavoritosPorAdoptante($this->table, $adoptanteId);
    }

    /**
     * Elimina un favorito por su ID y adoptante_id (seguridad).
     */
    public function eliminar(int $favoritoId, int $adoptanteId): bool
    {
        return $this->queryBuilder->eliminarFavorito($this->table, $favoritoId, $adoptanteId);
    }
    public function getFavoritosIds(?array $sessionUser): array
    {
        if (empty($sessionUser) || !isset($sessionUser['rol']) || $sessionUser['rol'] !== 'adoptante' || empty($sessionUser['id'])) {
            return [];
        }

        $favoritos = $this->getByAdoptanteId((int)$sessionUser['id']);
        return array_column($favoritos, 'id');
    }
}
