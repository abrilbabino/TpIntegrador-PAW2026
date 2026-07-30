<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Paw\Core\Exceptions\ModelNotFoundException;

class Refugio extends Model
{
    public $table = 'refugio';
    public $fields = [
        'usuario_id' => null,
        'ubicacion_id' => null,
        'nombre_institucion' => '',
        'cuit' => '',
        'cvu' => null,
        'alias' => null,
        'imagen' => 'default-refugio.jpg',
        'telefono' => '',
        'email' => null,
        'ciudad' => null,
        'provincia' => null,
        'direccion' => null,
        'descripcion' => null,
        'adoptables_disponibles' => 0,
    ];

    public function set(array $values)
    {
        foreach (array_keys($this->fields) as $field) {
            if (!isset($values[$field])) {
                continue;
            }
            $this->fields[$field] = $values[$field];
        }
    }

    public function load($id)
    {
        if (!is_numeric($id) || $id < 0) {
            throw new InvalidValueFormatException("El ID del refugio debe ser un entero mayor a 0");
        }

        $record = $this->queryBuilder->obtenerRefugioConUbicacion($id);

        if ($record) {
            $this->set($record);
        } else {
            throw new ModelNotFoundException("No se encontró un refugio con el ID proporcionado");
        }
    }

    public function getId(): int
    {
        return (int) $this->fields['usuario_id'];
    }

    public function getNombre(): string
    {
        return $this->fields['nombre_institucion'];
    }

    public function getAlias(): ?string
    {
        return $this->fields['alias'];
    }

    public function getDescripcion(): ?string
    {
        return $this->fields['descripcion'] ?? 'Este refugio aún no tiene una descripción detallada, pero trabaja arduamente día a día para rescatar y cuidar a los animales que más lo necesitan.';
    }

    public function getCvu(): ?string
    {
        return $this->fields['cvu'];
    }

    public function getEmail(): ?string
    {
        return $this->fields['email'];
    }

    public function getEncuestas(): array
    {
        return $this->queryBuilder->obtenerEncuestasPorRefugio($this->getId());
    }

    public function getFotosSeguimiento(): array
    {
        return $this->queryBuilder->obtenerFotosSeguimientoPorRefugio($this->getId());
    }

    public function getSeguimientoAgrupado(): array
    {
        $encuestas = $this->getEncuestas();
        $fotosSeguimiento = $this->getFotosSeguimiento();
        
        $seguimientoAgrupado = [];
        foreach ($encuestas as $enc) {
            $mId = $enc['mascota_id'];
            if (!isset($seguimientoAgrupado[$mId])) {
                $seguimientoAgrupado[$mId] = [
                    'mascota_nombre' => $enc['mascota_nombre'],
                    'adoptante_nombre' => $enc['adoptante_nombre'] ?? 'Desconocido',
                    'encuestas' => [],
                    'fotos' => []
                ];
            }
            $seguimientoAgrupado[$mId]['encuestas'][] = $enc;
        }
        
        foreach ($fotosSeguimiento as $foto) {
            $mId = $foto['mascota_id'];
            if (!isset($seguimientoAgrupado[$mId])) {
                $seguimientoAgrupado[$mId] = [
                    'mascota_nombre' => $foto['mascota_nombre'],
                    'adoptante_nombre' => $foto['adoptante_nombre'] ?? 'Desconocido',
                    'encuestas' => [],
                    'fotos' => []
                ];
            }
            $seguimientoAgrupado[$mId]['fotos'][] = $foto;
        }
        
        return $seguimientoAgrupado;
    }
}
