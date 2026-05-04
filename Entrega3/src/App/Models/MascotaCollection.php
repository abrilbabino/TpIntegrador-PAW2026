<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class MascotaCollection extends Model
{
    public $table = 'mascota';

    public function getAll(array $filtros = [])
    {
        $mascotas = $this->queryBuilder->select($this->table, $filtros);
        return $this->mapearMascotas($mascotas);
    }

    public function get($id){
        $mascota = new Mascota;
        $mascota->setQueryBuilder($this->queryBuilder);
        $mascota->load($id);
        return $mascota;
    }

    private function mapearMascotas(array $rows): array
    {
        $coleccion = [];
        foreach ($rows as $row) {
            $mascota = new Mascota;
            $mascota->set($row);
            $coleccion[] = $mascota;
        }
        return $coleccion;
    }
}
