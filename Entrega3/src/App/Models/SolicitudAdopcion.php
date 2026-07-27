<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\ModelNotFoundException;

class SolicitudAdopcion extends Model
{
    public $table = 'solicitud_de_adopcion';

    public $fields = [
        'adoptante_id' => null,
        'nombre' => null,
        'apellido' => null,
        'email' => null,
        'telefono' => null,
        'fecha_nacimiento' => null,
        'mascota_id' => null,
        'acepta_contrato' => false,
    ];

    public function set(array $datos)
    {
        $this->fields['adoptante_id'] = $datos['adoptante_id'] ?? null;
        $this->fields['nombre'] = $datos['nombre'] ?? null;
        $this->fields['apellido'] = $datos['apellido'] ?? null;
        $this->fields['email'] = $datos['email'] ?? null;
        $this->fields['telefono'] = $datos['telefono'] ?? null;
        $this->fields['fecha_nacimiento'] = $datos['fecha_nacimiento'] ?? null;
        $this->fields['mascota_id'] = $datos['mascota_id'] ?? null;
        $this->fields['acepta_contrato'] = isset($datos['acepta_contrato']) && ($datos['acepta_contrato'] === 'on' || $datos['acepta_contrato'] === true || $datos['acepta_contrato'] === 1);
    }

    public function validar()
    {
        $errores = [];

        if (empty($this->fields['nombre'])) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }
        if (empty($this->fields['apellido'])) {
            $errores['apellido'] = 'El apellido es obligatorio.';
        }
        if (!filter_var($this->fields['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato de email no es válido.';
        }
        if (empty($this->fields['telefono'])) {
            $errores['telefono'] = 'El teléfono es obligatorio.';
        }
        if (empty($this->fields['mascota_id'])) {
            $errores['mascota_id'] = 'La mascota no es válida.';
        }
        if (empty($this->fields['acepta_contrato'])) {
            $errores['acepta_contrato'] = 'Debe aceptar los términos del contrato de adopción y seguimiento sanitario.';
        }

        if (!empty($this->fields['fecha_nacimiento'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $this->fields['fecha_nacimiento']);
            if (!$d || $d->format('Y-m-d') !== $this->fields['fecha_nacimiento']) {
                $errores['fecha_nacimiento'] = 'Fecha de nacimiento inválida.';
            } elseif ($d > new \DateTime()) {
                $errores['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura.';
            }
        }

        // Verificar solicitud duplicada contra la base de datos
        if (!empty($this->fields['adoptante_id']) && !empty($this->fields['mascota_id'])) {
            if ($this->existeSolicitud((int)$this->fields['adoptante_id'], (int)$this->fields['mascota_id'])) {
                $errores['solicitud_duplicada'] = 'Ya enviaste una solicitud de adopción para esta mascota. No es posible enviar más de una solicitud por mascota.';
            }
        }

        return $errores;
    }

    /**
     * Verifica si ya existe una solicitud de adopción del adoptante para la mascota indicada.
     */
    public function existeSolicitud(int $adoptanteId, int $mascotaId): bool
    {
        return $this->queryBuilder->exists($this->table, [
            'adoptante_id' => $adoptanteId,
            'mascota_id'   => $mascotaId,
        ]);
    }

    public function guardar(int $refugio_id)
    {
        $data = [
            'adoptante_id' => $this->fields['adoptante_id'],
            'mascota_id' => $this->fields['mascota_id'],
            'refugio_id' => $refugio_id,
            'fecha' => date('Y-m-d H:i:s'),
            'estado' => 'PENDIENTE',
            'contrato_aceptado' => 1,
            'fecha_aceptacion' => date('Y-m-d H:i:s')
        ];

        $this->queryBuilder->insert($this->table, $data);
    }

    /**
     * Procesa una solicitud de adopción (aceptar o rechazar)
     * Lanza excepciones con código HTTP si hay errores de validación.
     */
    public function procesarSolicitud(int $solicitudId, string $accion, int $refugioId): string
    {
        if ($accion !== 'aceptar' && $accion !== 'rechazar') {
            throw new \Exception('Acción no válida. Solo se permite aceptar o rechazar.', 400);
        }

        $solicitud = $this->queryBuilder->selectOne($this->table, ['id' => $solicitudId]);

        if (!$solicitud) {
            throw new ModelNotFoundException('No se encontró la solicitud especificada.');
        }

        if ((int)$solicitud['refugio_id'] !== $refugioId) {
            throw new \Exception('No tiene permisos para modificar esta solicitud.', 403);
        }

        if (strtoupper($solicitud['estado'] ?? '') !== 'PENDIENTE') {
            throw new \Exception('La solicitud ya ha sido procesada previamente.', 400);
        }

        $nuevoEstado = ($accion === 'aceptar') ? 'APROBADA' : 'RECHAZADA';
        $mascotaId = ($accion === 'aceptar') ? (int)$solicitud['mascota_id'] : null;

        try {
            $this->queryBuilder->procesarSolicitudAdopcion($this->table, $solicitudId, $nuevoEstado, $mascotaId, date('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar la base de datos: ' . $e->getMessage(), 500);
        }

        return $nuevoEstado;
    }
}
