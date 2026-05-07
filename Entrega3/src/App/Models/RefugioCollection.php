<?php

namespace Paw\App\Models;

use Paw\Core\Pagination;
use Paw\App\Models\Refugio;
use Paw\Core\Model;

class RefugioCollection extends Model
{
    public $table = 'refugio';

    public function count(array $filtros = []): int
    {
        return $this->queryBuilder->obtenerRefugiosFiltrados($this->table, $filtros, true);
    }

    public function getProvincias(): array { return $this->obtenerUbicacionUnica('provincia'); }
    public function getCiudades(): array { return $this->obtenerUbicacionUnica('ciudad'); }

    private function obtenerUbicacionUnica(string $campo): array
    {
        $camposPermitidos = ['provincia', 'ciudad'];
        if (!in_array($campo, $camposPermitidos)) {
            return [];
        }

        $resultados = $this->queryBuilder->obtenerUbicacionUnicaRefugio($this->table, $campo);
        
        return $this->mapearCampoRefugio($resultados, $campo);
    }

    public function getPaginated(array $filtros, int $pagina, int $porPagina = 6): array
    {
        $total = $this->count($filtros);
        $paginacion = new Pagination($pagina, $porPagina, $total);

        $resultados = $this->queryBuilder->obtenerRefugiosFiltrados($this->table, $filtros, false, $paginacion->perPage, $paginacion->offset);

        return [
            'items' => $this->mapRefugios($resultados),
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

    public function get($id){
        $refugio = new Refugio();
        $refugio->setQueryBuilder($this->queryBuilder);
        $refugio->load($id);
        return $refugio;
    }
}
