<?php

declare(strict_types=1);

namespace Paw\App\Models;

use Paw\Core\Model;

class RegistroSanitarioCollection extends Model
{
    public function getByMascota(int $mascotaId, array $filtros = []): array
    {
        $registros = $this->queryBuilder->obtenerRegistrosSanitarios($mascotaId, $filtros);

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

    public function pendientes($registros,$hoy){
        $proximos=[];
        foreach ($registros as $registro){
            if ($registro->fields['estado'] === 'PENDIENTE' && $registro->fields['fecha_programada'] >= $hoy) {
                $proximos[] = $registro;
            }
        }
        return $proximos;
    }
    
    public function completos($registros,$hoy){
        $historial=[];
        foreach ($registros as $registro){
            if ($registro->fields['estado'] === 'COMPLETADO' && $registro->fields['fecha_programada'] <= $hoy) {
                $historial[] = $registro;
            }
        }
        return $historial;
    }
}
