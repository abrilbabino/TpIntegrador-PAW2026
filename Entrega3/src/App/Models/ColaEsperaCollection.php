<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Traits\Notificable;
use Paw\App\Models\DiccionarioCollection;

class ColaEsperaCollection extends Model
{
    use Notificable;

    public string $table = 'cola_espera';

    //Agrega una nueva suscripción a la cola de espera.
    public function agregar(int $usuarioId, array $filtros): string|false
    {
        $camposPermitidos = ['especie', 'raza', 'tamano', 'temperamento', 'provincia', 'ciudad'];
        $datosAInsertar = ['usuario_id' => $usuarioId];

        $dicc = new DiccionarioCollection();
        $dicc->setQueryBuilder($this->queryBuilder);

        foreach ($camposPermitidos as $campo) {
            if (!empty($filtros[$campo])) {
                if (in_array($campo, ['especie', 'tamano', 'temperamento'])) {
                    $datosAInsertar[$campo . '_id'] = $dicc->obtenerOCrearId($campo, $filtros[$campo]);
                } else {
                    $datosAInsertar[$campo] = $filtros[$campo];
                }
            } else {
                if (in_array($campo, ['especie', 'tamano', 'temperamento'])) {
                    $datosAInsertar[$campo . '_id'] = null;
                } else {
                    $datosAInsertar[$campo] = null;
                }
            }
        }
        
        // Rango de edad
        $datosAInsertar['edad_min'] = isset($filtros['edad_min']) && $filtros['edad_min'] !== '' ? (int)$filtros['edad_min'] : null;
        $datosAInsertar['edad_max'] = isset($filtros['edad_max']) && $filtros['edad_max'] !== '' ? (int)$filtros['edad_max'] : null;

        if ($this->existeSuscripcion($datosAInsertar)) {
            return false;
        }

        return $this->queryBuilder->insert($this->table, $datosAInsertar);
    }

    //Verifica si existe una suscripción exactamente igual para el mismo usuario.
    private function existeSuscripcion(array $datos): bool
    {
        return $this->queryBuilder->existsExact($this->table, $datos);
    }

    // Compara una mascota recién creada con todas las suscripciones en la cola de espera.
    public function verificarMatches(int $mascotaId, array $mascotaData): void
    {
        $suscripciones = $this->queryBuilder->select($this->table, []);

        $dicc = new DiccionarioCollection();
        $dicc->setQueryBuilder($this->queryBuilder);

        // Convertir strings de mascotaData a IDs para compararlos
        $especieIdMascota = !empty($mascotaData['especie']) ? $dicc->obtenerOCrearId('especie', $mascotaData['especie']) : null;
        $tamanoIdMascota = !empty($mascotaData['tamano']) ? $dicc->obtenerOCrearId('tamano', $mascotaData['tamano']) : null;
        $temperamentoIdMascota = !empty($mascotaData['temperamento']) ? $dicc->obtenerOCrearId('temperamento', $mascotaData['temperamento']) : null;

        $edadMascota = isset($mascotaData['edad']) && $mascotaData['edad'] !== '' ? (int)$mascotaData['edad'] : null;

        foreach ($suscripciones as $s) {
            $match = true;
            
            // 1. Verificar campos de Diccionario (IDs)
            if (!empty($s['especie_id']) && (int)$s['especie_id'] !== $especieIdMascota) $match = false;
            if (!empty($s['tamano_id']) && (int)$s['tamano_id'] !== $tamanoIdMascota) $match = false;
            if (!empty($s['temperamento_id']) && (int)$s['temperamento_id'] !== $temperamentoIdMascota) $match = false;

            // 2. Verificar campos de texto (raza, provincia, ciudad)
            $camposTexto = ['raza', 'provincia', 'ciudad'];
            foreach ($camposTexto as $campo) {
                if ($match && !empty($s[$campo])) {
                    $valorFiltro = strtolower(trim($s[$campo]));
                    $valorMascota = strtolower(trim($mascotaData[$campo] ?? ''));
                    if ($valorFiltro !== $valorMascota) {
                        $match = false;
                    }
                }
            }

            // 3. Verificar edad
            if ($match && $edadMascota !== null) {
                if ($s['edad_min'] !== null && $edadMascota < (int)$s['edad_min']) {
                    $match = false;
                }
                if ($s['edad_max'] !== null && $edadMascota > (int)$s['edad_max']) {
                    $match = false;
                }
            }

            if ($match) {
                // Hay match
                $nombreMascota = ucfirst($mascotaData['nombre'] ?? 'Una mascota');
                $titulo = '¡Match Encontrado!';
                $mensaje = $nombreMascota . ' acaba de ingresar y coincide con tu alerta.';
                $enlace = '/mascota?id=' . $mascotaId;
                
                try {
                    $this->notificar((int) $s['usuario_id'], $titulo, $mensaje, $enlace);
                    // Eliminar la suscripción
                    $this->queryBuilder->delete($this->table, ['id' => $s['id']]);
                } catch (\Exception $e) {
                    error_log("Error al notificar Cola de Espera: " . $e->getMessage());
                }
            }
        }
    }

    public function getByUsuarioId(int $usuarioId, ?int $limit = null, ?int $offset = null): array
    {
        return $this->queryBuilder->obtenerAlertasPorUsuario($this->table, $usuarioId, $limit, $offset);
    }

    public function getTotalByUsuarioId(int $usuarioId): int
    {
        return $this->queryBuilder->contarAlertasPorUsuario($this->table, $usuarioId);
    }

    public function delete(int $id, int $usuarioId): bool
    {
        return $this->queryBuilder->delete($this->table, [
            'id' => $id,
            'usuario_id' => $usuarioId
        ]);
    }
}
