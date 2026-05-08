<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Pagination;
use Paw\App\Models\Mascota;

class MascotaCollection extends Model
{
    public string $table = 'mascota';

    private array $camposPermitidosParaFiltro = ['tamano', 'especie', 'temperamento'];

    public function getAll(array $filtros = []): array
    {
        $mascotas = $this->queryBuilder->select($this->table, $filtros);
        return $this->mapMascotas($mascotas);
    }

    public function get($id)
    {
        $mascota = new Mascota;
        $mascota->setQueryBuilder($this->queryBuilder);
        $mascota->load($id);
        return $mascota;    
    }

    public function getTamanos(): array { return $this->getCampoUnico('tamano'); }
    public function getEspecies(): array { return $this->getCampoUnico('especie'); }
    public function getTemperamentos(): array { return $this->getCampoUnico('temperamento'); }
    public function getProvincias(): array { return $this->mapearCampoMascota($this->queryBuilder->obtenerUbicacionUnicaRefugio('refugio', 'provincia'), 'provincia'); }
    public function getCiudades(): array { return $this->mapearCampoMascota($this->queryBuilder->obtenerUbicacionUnicaRefugio('refugio', 'ciudad'), 'ciudad'); }

    private function getCampoUnico(string $campo): array
    {
        if (!in_array($campo, $this->camposPermitidosParaFiltro)) {
            return [];
        }

        $resultados = $this->queryBuilder->obtenerValoresUnicos($this->table, $campo);
        
        return $this->mapearCampoMascota($resultados, $campo);
    }

    public function buscar(string $termino): array
    {
        $resultados = $this->queryBuilder->buscarMascotasPorTermino($this->table, $termino);
        return $this->mapMascotas($resultados);
    }

    public function buscarPaginated(string $termino, int $pagina, int $porPagina = 6): array
    {
        $total = $this->queryBuilder->buscarMascotasPorTermino($this->table, $termino, true); 

        $paginacion = new Pagination($pagina, $porPagina, $total);

        $resultados = $this->queryBuilder->buscarMascotasPorTermino($this->table, $termino, false, $paginacion->perPage, $paginacion->offset);

        return [
            'items' => $this->mapMascotas($resultados),
            'pagination' => $paginacion,
        ];
    }

    public function count(array $filtros = []): int
    {
        return $this->queryBuilder->obtenerMascotasFiltradas($filtros, true);
    }

    public function getPaginated(array $filtros, int $pagina, int $porPagina = 6): array
    {
        $total = $this->count($filtros);
        $paginacion = new Pagination($pagina, $porPagina, $total);
        
        $mascotas = $this->queryBuilder->obtenerMascotasFiltradas($filtros, false, $paginacion->perPage, $paginacion->offset);

        return [
            'items' => $this->mapMascotas($mascotas), 'pagination' => $paginacion,
        ];
    }

    private function mapearCampoMascota(array $filas, string $campo): array
    {
        $mascotas = [];
        foreach ($filas as $fila) {
            $mascota = new Mascota();
            $mascota->fields[$campo] = $fila[$campo];
            $mascotas[] = $mascota;
        }
        return $mascotas;
    }

    private function mapMascotas(array $filas): array
    {
        $coleccion = [];
        foreach ($filas as $fila) {
            $mascota = new Mascota();
            $mascota->set($fila);
            $coleccion[] = $mascota;
        }
        return $coleccion;
    }
}