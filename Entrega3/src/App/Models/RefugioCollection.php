<?php

namespace Paw\App\Models;

use Paw\Core\Pagination;

class RefugioCollection extends Refugio
{
    public $table = 'refugio';

    public function getPaginated(array $filtros, int $page, int $perPage = 6): array
    {
        $total = $this->count($filtros);
        $pagination = new Pagination($page, $perPage, $total);

        $sql = "SELECT r.id, r.nombre_institucion, r.cuit, r.imagen, r.telefono, u.ciudad, u.provincia, 
                       (SELECT COUNT(*) FROM mascota m WHERE m.refugio_id = r.id AND m.estado_adopcion = 'DISPONIBLE') as adoptables_disponibles
                FROM refugio r
                LEFT JOIN ubicacion u ON r.ubicacion_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['provincia'])) {
            $sql .= " AND u.provincia = :provincia";
            $params[':provincia'] = $filtros['provincia'];
        }

        if (!empty($filtros['ciudad'])) {
            $sql .= " AND u.ciudad = :ciudad";
            $params[':ciudad'] = $filtros['ciudad'];
        }

        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND (u.ciudad LIKE :ubicacion OR u.provincia LIKE :ubicacion2)";
            $params[':ubicacion'] = '%' . $filtros['ubicacion'] . '%';
            $params[':ubicacion2'] = '%' . $filtros['ubicacion'] . '%';
        }

        $sql .= " ORDER BY r.nombre_institucion ASC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $pagination->perPage;
        $params[':offset'] = $pagination->offset;

        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        foreach ($params as $key => $val) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $val, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $this->mapRefugios($result),
            'pagination' => $pagination,
        ];
    }

    public function count(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM refugio r
                LEFT JOIN ubicacion u ON r.ubicacion_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['provincia'])) {
            $sql .= " AND u.provincia = :provincia";
            $params[':provincia'] = $filtros['provincia'];
        }

        if (!empty($filtros['ciudad'])) {
            $sql .= " AND u.ciudad = :ciudad";
            $params[':ciudad'] = $filtros['ciudad'];
        }

        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND (u.ciudad LIKE :ubicacion OR u.provincia LIKE :ubicacion2)";
            $params[':ubicacion'] = '%' . $filtros['ubicacion'] . '%';
            $params[':ubicacion2'] = '%' . $filtros['ubicacion'] . '%';
        }

        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) ($result['total'] ?? 0);
    }

    public function getProvincias()
    {
        $sql = "SELECT DISTINCT provincia FROM ubicacion WHERE provincia IS NOT NULL AND provincia != '' ORDER BY provincia ASC";
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->mapToRefugioField($result, 'provincia');
    }

    public function getCiudades()
    {
        $sql = "SELECT DISTINCT ciudad FROM ubicacion WHERE ciudad IS NOT NULL AND ciudad != '' ORDER BY ciudad ASC";
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->mapToRefugioField($result, 'ciudad');
    }

    private function mapRefugios(array $rows): array
    {
        $coleccion = [];
        foreach ($rows as $row) {
            $refugio = new Refugio();
            $refugio->set($row);
            $coleccion[] = $refugio;
        }
        return $coleccion;
    }

    private function mapToRefugioField(array $rows, string $field): array
    {
        $refugios = [];
        foreach ($rows as $row) {
            $refugio = new Refugio();
            $refugio->fields[$field] = $row[$field];
            $refugios[] = $refugio;
        }
        return $refugios;
    }
}
