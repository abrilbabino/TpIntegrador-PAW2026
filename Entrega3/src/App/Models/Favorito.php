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
        $sql = "SELECT f.id AS favorito_id, m.*
                FROM {$this->table} f
                INNER JOIN mascota m ON m.id = f.mascota_id
                WHERE f.adoptante_id = :adoptante_id
                ORDER BY f.id DESC";

        $sentencia = $this->queryBuilder->getConnection()->prepare($sql);
        $sentencia->bindValue(':adoptante_id', $adoptanteId, \PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Elimina un favorito por su ID y adoptante_id (seguridad).
     */
    public function eliminar(int $favoritoId, int $adoptanteId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND adoptante_id = :adoptante_id";
        $sentencia = $this->queryBuilder->getConnection()->prepare($sql);
        $sentencia->bindValue(':id', $favoritoId, \PDO::PARAM_INT);
        $sentencia->bindValue(':adoptante_id', $adoptanteId, \PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->rowCount() > 0;
    }
}
