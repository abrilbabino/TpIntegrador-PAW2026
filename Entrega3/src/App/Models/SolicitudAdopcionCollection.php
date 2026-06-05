<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class SolicitudAdopcionCollection extends Model
{
    public string $table = 'solicitud_de_adopcion';

    public function getSolicitudesAdoptante(int $adoptanteId): array
    {
        return $this->queryBuilder->obtenerSolicitudesPorAdoptante($this->table, $adoptanteId);
    }

    public function getAdopcionesAdoptante(int $adoptanteId): array
    {
        return $this->queryBuilder->obtenerAdopcionesPorAdoptante($this->table, $adoptanteId);
    }

    public function getById(int $id)
    {
        return $this->queryBuilder->selectOne($this->table, ['id' => $id]);
    }

    public function getSolicitudesRefugio(int $refugioId): array
    {
        return $this->queryBuilder->obtenerSolicitudesPorRefugio($this->table, $refugioId);
    }
}
