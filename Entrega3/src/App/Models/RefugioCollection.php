<?php

namespace Paw\App\Models;


use Paw\App\Models\Refugio;
use Paw\Core\Model;

class RefugioCollection extends Model
{
    public $table = 'refugio';

    public function count(array $filtros = []): int
    {
        return $this->queryBuilder->obtenerRefugiosFiltrados($this->table, $filtros, true);
    }

    public function getAll() {
        $sql = "SELECT r.*, u.ciudad, u.provincia, 
                       (SELECT COUNT(*) FROM mascota m WHERE m.refugio_id = r.usuario_id AND m.estado_adopcion = 'DISPONIBLE') as adoptables_disponibles
                FROM {$this->table} r 
                LEFT JOIN ubicacion u ON r.usuario_id = u.refugio_id 
                ORDER BY r.nombre_institucion ASC";
        $rows = $this->queryBuilder->rawQuery($sql);
        return $this->mapRefugios($rows);
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

    public function buscar(string $termino): array
    {
        $resultados = $this->queryBuilder->buscarRefugiosPorTermino($this->table, $termino);
        return $this->mapRefugios($resultados);
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

    public function getRefugiosConUbicacion(array $filtros = []): array
    {
        return $this->queryBuilder->obtenerRefugiosConUbicacion($this->table, $filtros);
    }
}
