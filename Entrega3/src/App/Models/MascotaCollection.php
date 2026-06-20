<?php

namespace Paw\App\Models;

use Paw\Core\Model;

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

    public function buscarCompatibles(array $filtros): array
    {
        $resultadosDB = $this->queryBuilder->selectCompatibles($this->table, $filtros);
        return $this->mapMascotas($resultadosDB);
    }

    public function getFiltered(array $filtros): array
    {
        $resultados = $this->queryBuilder->obtenerMascotasFiltradas($filtros);
        return $this->mapMascotas($resultados);
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

    public function getAdopcionesByAdoptante(int $adoptanteId): array
    {
        $resultados = $this->queryBuilder->obtenerAdopcionesPorAdoptante('solicitud_de_adopcion', $adoptanteId);
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
    
    public function getByRefugioId(int $refugioId): array
    {
        $mascotas = $this->queryBuilder->selectByRefugioId($this->table, $refugioId);
        return $this->mapMascotas($mascotas);
    }

    public function verificarPermisosLibreta(int $mascotaId, int $usuarioId, string $rol): bool
    {
        $mascota = $this->get($mascotaId);
        
        if (!$mascota || empty($mascota->fields['id'])) {
            return false;
        }

        $estadoAdopcion = $mascota->fields['estado_adopcion'] ?? 'DISPONIBLE';

        if ($estadoAdopcion === 'ADOPTADO') {
            if ($rol === 'refugio') {
                return (int)$mascota->fields['refugio_id'] === $usuarioId;
            }
            if ($rol !== 'adoptante') {
                return false;
            }
            
            // Verificar si este usuario tiene la solicitud aprobada (APROBADO o APROBADA)
            return $this->queryBuilder->exists('solicitud_de_adopcion', [
                'mascota_id' => $mascotaId,
                'adoptante_id' => $usuarioId,
                'estado' => 'APROBADO'
            ]) || $this->queryBuilder->exists('solicitud_de_adopcion', [
                'mascota_id' => $mascotaId,
                'adoptante_id' => $usuarioId,
                'estado' => 'APROBADA'
            ]);
        } else {
            if ($rol !== 'refugio') {
                return false;
            }
            return (int)$mascota->fields['refugio_id'] === $usuarioId;
        }
    }

    public function obtenerPermisosLibreta(int $mascotaId, int $usuarioId, string $rol): array
    {
        $permisos = [
            'puedeModificar' => false,
            'puedeAgregar'   => false
        ];

        if (!$this->verificarPermisosLibreta($mascotaId, $usuarioId, $rol)) {
            return $permisos;
        }

        $mascota = $this->get($mascotaId);
        $estadoAdopcion = $mascota->fields['estado_adopcion'] ?? 'DISPONIBLE';

        if ($estadoAdopcion === 'ADOPTADO') {
            if ($rol === 'adoptante') {
                $permisos['puedeModificar'] = true;
                $permisos['puedeAgregar'] = false;
            } elseif ($rol === 'refugio') {
                $permisos['puedeModificar'] = false;
                $permisos['puedeAgregar'] = true;
            }
        } else {
            if ($rol === 'refugio') {
                $permisos['puedeModificar'] = true;
                $permisos['puedeAgregar'] = true;
            }
        }

        return $permisos;
    }

    public function obtenerMascotasApiData(array $favoritosIds = []): array
    {
        $sql = "SELECT m.id, m.nombre, m.imagen, m.edad, m.tamano, m.temperamento, m.especie, m.refugio_id, 
                       u.provincia, u.ciudad
                FROM mascota m
                LEFT JOIN ubicacion u ON m.refugio_id = u.refugio_id
                WHERE m.estado_adopcion = 'DISPONIBLE'";

        $rows = $this->queryBuilder->rawQuery($sql);

        $mascotasData = [];
        foreach ($rows as $row) {
            $mascotasData[] = [
                'id'           => $row['id'],
                'nombre'       => $row['nombre'],
                'imagen'       => $row['imagen'],
                'edad'         => $row['edad'],
                'tamano'       => $row['tamano'],
                'temperamento' => $row['temperamento'],
                'especie'      => $row['especie'],
                'refugio_id'   => $row['refugio_id'],
                'provincia'    => $row['provincia'],
                'ciudad'       => $row['ciudad'],
                'es_favorito'  => in_array($row['id'], $favoritosIds)
            ];
        }

        return $mascotasData;
    }
}
