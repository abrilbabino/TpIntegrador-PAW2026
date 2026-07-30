<?php

declare(strict_types=1);

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Traits\Notificable;

class RegistroSanitarioCollection extends Model
{
    use Notificable;
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
    
    public function completos($registros, $hoy): array
    {
        $historial = [];
        foreach ($registros as $registro) {
            if ($registro->fields['estado'] === 'COMPLETADO') {
                $historial[] = $registro;
            }
        }
        return $historial;
    }

    public function crearRegistroSanitario(array $data)
    {
        $this->queryBuilder->insert('registro_sanitario', $data);

        if (isset($data['mascota_id'])) {
            $mascota = $this->queryBuilder->selectOne('mascota', ['id' => $data['mascota_id']]);
            if ($mascota) {
                $solicitud = $this->queryBuilder->selectOne('solicitud_de_adopcion', [
                    'mascota_id' => $mascota['id'],
                    'estado' => 'APROBADA'
                ]);
                
                if ($solicitud) {
                    $tituloReg = $data['titulo'] ?? 'un nuevo registro sanitario';
                    $nombreMascota = $mascota['nombre'] ?? 'tu mascota';
                    $this->notificar(
                        (int)$solicitud['adoptante_id'],
                        "Nuevo Registro Sanitario",
                        "El refugio ha programado {$tituloReg} para {$nombreMascota}.",
                        "/mascota/libreta?id=" . $mascota['id']
                    );
                }
            }
        }
    }

    public function completarRegistroSanitario(int $registroId, string $rutaRelativa, string $fecha, array $userSession = [])
    {
        $this->queryBuilder->actualizarArchivoRegistroSanitario($registroId, $rutaRelativa, $fecha);

        if (empty($userSession)) return;

        $registro = $this->queryBuilder->selectOne('registro_sanitario', ['id' => $registroId]);
        if (!$registro) return;

        $mascota = $this->queryBuilder->selectOne('mascota', ['id' => $registro['mascota_id']]);
        if (!$mascota) return;

        $tituloReg = $registro['titulo'] ?? 'un registro sanitario';
        $nombreMascota = $mascota['nombre'] ?? 'tu mascota';

        if (($userSession['rol'] ?? '') === 'adoptante') {
            $this->notificar(
                (int)$mascota['refugio_id'],
                "Registro Sanitario Completado",
                "El adoptante ha subido el comprobante de {$tituloReg} para {$nombreMascota}.",
                "/mascota/libreta?id=" . $mascota['id']
            );
        }
    }
}
