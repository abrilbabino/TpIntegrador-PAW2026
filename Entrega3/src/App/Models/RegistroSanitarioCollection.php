<?php

declare(strict_types=1);

namespace Paw\App\Models;

use Paw\Core\Model;

class RegistroSanitarioCollection extends Model
{
    public function getByMascota(int $mascotaId, array $filtros = []): array
    {
        $sql = "SELECT * FROM registro_sanitario WHERE mascota_id = :mascota_id";
        $binds = [':mascota_id' => $mascotaId];

        if (!empty($filtros['categoria'])) {
            $sql .= " AND tipo = :tipo";
            $binds[':tipo'] = $filtros['categoria'];
        }

        if (!empty($filtros['anio'])) {
            $sql .= " AND YEAR(fecha_programada) = :anio";
            $binds[':anio'] = $filtros['anio'];
        }

        $sql .= " ORDER BY fecha_programada DESC";

        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        foreach ($binds as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $objetos = [];
        foreach ($registros as $row) {
            $objeto = $this->instanciarPorTipo($row['tipo']);
            $objeto->set($row);
            $objetos[] = $objeto;
        }

        return $objetos;
    }

    private function instanciarPorTipo(string $tipo): RegistroSanitario
    {
        switch (strtolower($tipo)) {
            case 'vacuna':
                return new Vacuna();
            case 'desparasitacion':
                return new Desparasitacion();
            case 'cirugia':
                return new Cirugia();
            case 'tratamiento':
                return new Tratamiento();
            case 'chequeo':
                return new Chequeo();
            default:
                // Fallback a Chequeo si hay un error
                return new Chequeo();
        }
    }
}
