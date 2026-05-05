<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Pagination;

class MascotaCollection extends Model
{
    public $table = 'mascota';

    public function getAll(array $filtros = [])
    {

        $mascotas = $this->queryBuilder->select($this->table, $filtros);
        return $this->mapMascotas($mascotas);
    }

    public function get($id){
        $mascota = new Mascota;
        $mascota->setQueryBuilder($this->queryBuilder);
        $mascota->load($id);
        return $mascota;	
    }

    public function getTamanos()
    {
        $sql = "SELECT DISTINCT tamano FROM mascota WHERE tamano IS NOT NULL AND tamano != '' ORDER BY tamano";
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->mapToMascotaField($result, 'tamano');
    }

    public function getEspecies()
    {
        $sql = "SELECT DISTINCT especie FROM mascota WHERE especie IS NOT NULL AND especie != '' ORDER BY especie";
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->mapToMascotaField($result, 'especie');
    }

    public function getTemperamentos()
    {
        $sql = "SELECT DISTINCT temperamento FROM mascota WHERE temperamento IS NOT NULL AND temperamento != '' ORDER BY temperamento";
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->mapToMascotaField($result, 'temperamento');
    }

    private function mapToMascotaField(array $rows, string $field): array
    {
        $mascotas = [];
        foreach ($rows as $row) {
            $mascota = new Mascota();
            $mascota->fields[$field] = $row[$field];
            $mascotas[] = $mascota;
        }
        return $mascotas;
    }

    public function buscar($termino)
    {
        $sql = "SELECT * FROM mascota WHERE estado_adopcion = :estado AND (nombre LIKE :term1 OR especie LIKE :term2 OR descripcion LIKE :term3)";
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->bindValue(':estado', 'DISPONIBLE');
        $stmt->bindValue(':term1', "%{$termino}%");
        $stmt->bindValue(':term2', "%{$termino}%");
        $stmt->bindValue(':term3', "%{$termino}%");
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->mapMascotas($result);
    }

    public function count(array $filtros = []): int
    {
        $result = $this->queryBuilder->count($this->table, $filtros);
        return (int) ($result['total'] ?? 0);
    }

    public function getPaginated(array $filtros, int $page, int $perPage = 6): array
    {
        $total = $this->count($filtros);
        $pagination = new Pagination($page, $perPage, $total);
        
        $mascotas = $this->queryBuilder->select(
            $this->table,
            $filtros,
            [],
            $pagination->perPage,
            $pagination->offset
        );

        return [
            'items' => $this->mapMascotas($mascotas),
            'pagination' => $pagination,
        ];
    }

    public function buscarPaginated(string $termino, int $page, int $perPage = 6): array
    {
        // Contar resultados de búsqueda
        $sqlCount = "SELECT COUNT(*) as total FROM mascota WHERE estado_adopcion = :estado AND (nombre LIKE :term1 OR especie LIKE :term2 OR descripcion LIKE :term3)";
        $stmtCount = $this->queryBuilder->getConnection()->prepare($sqlCount);
        $stmtCount->bindValue(':estado', 'DISPONIBLE');
        $stmtCount->bindValue(':term1', "%{$termino}%");
        $stmtCount->bindValue(':term2', "%{$termino}%");
        $stmtCount->bindValue(':term3', "%{$termino}%");
        $stmtCount->execute();
        $total = (int) $stmtCount->fetch(\PDO::FETCH_ASSOC)['total'];

        $pagination = new Pagination($page, $perPage, $total);

        $sql = "SELECT * FROM mascota WHERE estado_adopcion = :estado AND (nombre LIKE :term1 OR especie LIKE :term2 OR descripcion LIKE :term3) LIMIT :limit OFFSET :offset";
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->bindValue(':estado', 'DISPONIBLE');
        $stmt->bindValue(':term1', "%{$termino}%");
        $stmt->bindValue(':term2', "%{$termino}%");
        $stmt->bindValue(':term3', "%{$termino}%");
        $stmt->bindValue(':limit', $pagination->perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination->offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $this->mapMascotas($result),
            'pagination' => $pagination,
        ];
    }

    private function mapMascotas(array $rows): array
    {
        $coleccion = [];
        foreach ($rows as $row) {
            $mascota = new Mascota();
            $mascota->set($row);
            $coleccion[] = $mascota;
        }
        return $coleccion;
    }
}