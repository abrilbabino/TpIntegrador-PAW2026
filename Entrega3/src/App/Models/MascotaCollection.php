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

    private function getCampoUnico(string $campo): array
    {
        if (!in_array($campo, $this->camposPermitidosParaFiltro)) {
            return [];
        }

        $sql = "SELECT DISTINCT {$campo} FROM {$this->table} WHERE {$campo} IS NOT NULL AND {$campo} != '' ORDER BY {$campo}";
        $sentencia = $this->queryBuilder->getConnection()->prepare($sql);
        $sentencia->execute();
        
        return $this->mapearCampoMascota($sentencia->fetchAll(\PDO::FETCH_ASSOC), $campo);
    }

    public function buscar(string $termino): array
    {
        $sentencia = $this->prepararConsultaBusqueda($termino);
        $sentencia->execute();
        return $this->mapMascotas($sentencia->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function buscarPaginated(string $termino, int $pagina, int $porPagina = 6): array
    {
        $sentenciaCount = $this->prepararConsultaBusqueda($termino, true);
        $sentenciaCount->execute();
        $total = (int) $sentenciaCount->fetchColumn(); 

        $paginacion = new Pagination($pagina, $porPagina, $total);

        $sentencia = $this->prepararConsultaBusqueda($termino, false, $paginacion->perPage, $paginacion->offset);
        $sentencia->execute();

        return [
            'items' => $this->mapMascotas($sentencia->fetchAll(\PDO::FETCH_ASSOC)),
            'pagination' => $paginacion,
        ];
    }

    private function prepararConsultaBusqueda(string $termino, bool $esConteo = false, ?int $limite = null, ?int $offset = null): \PDOStatement
    {
        $select = $esConteo ? "COUNT(*)" : "*";
        $sql = "SELECT {$select} FROM {$this->table} WHERE estado_adopcion = 'DISPONIBLE' 
                AND (nombre LIKE :term1 OR especie LIKE :term2 OR descripcion LIKE :term3)";
        
        if (!$esConteo && $limite !== null && $offset !== null) {
            $sql .= " LIMIT :limite OFFSET :offset";
        }

        $sentencia = $this->queryBuilder->getConnection()->prepare($sql);
        
        $terminoLike = "%{$termino}%";
        $sentencia->bindValue(':term1', $terminoLike);
        $sentencia->bindValue(':term2', $terminoLike);
        $sentencia->bindValue(':term3', $terminoLike);
        
        if (!$esConteo && $limite !== null && $offset !== null) {
            $sentencia->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $sentencia->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        return $sentencia;
    }

    public function count(array $filtros = []): int
    {
        $resultado = $this->queryBuilder->count($this->table, $filtros);
        return (int) ($resultado['total'] ?? 0);
    }

    public function getPaginated(array $filtros, int $pagina, int $porPagina = 6): array
    {
        $total = $this->count($filtros);
        $paginacion = new Pagination($pagina, $porPagina, $total);
        
        $mascotas = $this->queryBuilder->select(
            $this->table, $filtros, [], $paginacion->perPage, $paginacion->offset
        );

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