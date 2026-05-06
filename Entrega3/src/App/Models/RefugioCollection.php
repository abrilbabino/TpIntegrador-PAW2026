<?php

namespace Paw\App\Models;

use Paw\Core\Pagination;

class RefugioCollection extends Refugio
{
    public $table = 'refugio';

    public function count(array $filtros = []): int
    {
        $filtrosDin = $this->prepararFiltrosRefugio($filtros);

        $sql = "SELECT COUNT(*) FROM {$this->table} r WHERE 1=1 " . $filtrosDin['sql'];

        $sentencia = $this->queryBuilder->getConnection()->prepare($sql);
        foreach ($filtrosDin['params'] as $key => $val) {
            $sentencia->bindValue($key, $val);
        }
        $sentencia->execute();

        return (int) ($sentencia->fetchColumn() ?: 0);
    }

    private function prepararFiltrosRefugio(array $filtros): array
    {
        $sql = "";
        $params = [];

        if (!empty($filtros['provincia'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM ubicacion u2 WHERE u2.refugio_id = r.id AND u2.provincia = :provincia)";
            $params[':provincia'] = $filtros['provincia'];
        }

        if (!empty($filtros['ciudad'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM ubicacion u2 WHERE u2.refugio_id = r.id AND u2.ciudad = :ciudad)";
            $params[':ciudad'] = $filtros['ciudad'];
        }

        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM ubicacion u2 WHERE u2.refugio_id = r.id AND (u2.ciudad LIKE :ubicacion OR u2.provincia LIKE :ubicacion2))";
            $params[':ubicacion'] = '%' . $filtros['ubicacion'] . '%';
            $params[':ubicacion2'] = '%' . $filtros['ubicacion'] . '%';
        }

        return ['sql' => $sql, 'params' => $params];
    }

    public function getProvincias(): array { return $this->obtenerUbicacionUnica('provincia'); }
    public function getCiudades(): array { return $this->obtenerUbicacionUnica('ciudad'); }

    private function obtenerUbicacionUnica(string $campo): array
    {
        $camposPermitidos = ['provincia', 'ciudad'];
        if (!in_array($campo, $camposPermitidos)) {
            return [];
        }

        $sql = "SELECT DISTINCT u.{$campo} FROM ubicacion u 
                INNER JOIN {$this->table} r ON u.refugio_id = r.id
                WHERE u.{$campo} IS NOT NULL AND u.{$campo} != '' 
                ORDER BY u.{$campo} ASC";
                
        $sentencia = $this->queryBuilder->getConnection()->prepare($sql);
        $sentencia->execute();
        
        return $this->mapearCampoRefugio($sentencia->fetchAll(\PDO::FETCH_ASSOC), $campo);
    }

    public function getPaginated(array $filtros, int $pagina, int $porPagina = 6): array
    {
        $total = $this->count($filtros);
        $paginacion = new Pagination($pagina, $porPagina, $total);
        $filtrosDin = $this->prepararFiltrosRefugio($filtros);

        $sql = "SELECT r.id, r.nombre_institucion, r.cuit, r.imagen, r.telefono,
                       STRING_AGG(DISTINCT u.ciudad, ', ' ORDER BY u.ciudad ASC) as ciudad,
                       STRING_AGG(DISTINCT u.provincia, ', ' ORDER BY u.provincia ASC) as provincia,
                       (SELECT COUNT(*) FROM mascota m WHERE m.refugio_id = r.id AND m.estado_adopcion = 'DISPONIBLE') as adoptables_disponibles
                FROM {$this->table} r LEFT JOIN ubicacion u ON r.id = u.refugio_id WHERE 1=1 " . $filtrosDin['sql'] . "
                GROUP BY r.id, r.nombre_institucion, r.cuit, r.imagen, r.telefono
                ORDER BY r.nombre_institucion ASC 
                LIMIT :limit OFFSET :offset";

        $sentencia = $this->queryBuilder->getConnection()->prepare($sql);
        
        foreach ($filtrosDin['params'] as $key => $val) {
            $sentencia->bindValue($key, $val);
        }
        
        $sentencia->bindValue(':limit', $paginacion->perPage, \PDO::PARAM_INT);
        $sentencia->bindValue(':offset', $paginacion->offset, \PDO::PARAM_INT);
        
        $sentencia->execute();

        return [
            'items' => $this->mapRefugios($sentencia->fetchAll(\PDO::FETCH_ASSOC)),
            'pagination' => $paginacion,
        ];
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

    private function mapearCampoRefugio(array $rows, string $field): array
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
