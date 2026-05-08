<?php

declare(strict_types=1);

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\InvalidValueFormatException;

abstract class RegistroSanitario extends Model
{
    public $table = 'registro_sanitario';
    
    public $fields = [
        'id' => null,
        'mascota_id' => null,
        'tipo' => null,
        'titulo' => null,
        'fecha_programada' => null,
        'fecha_realizada' => null,
        'estado' => 'PENDIENTE',
        'observaciones' => null,
        'archivo_adjunto' => null,
        'notificado' => false,
    ];

    public function setId($id)
    {
        if (!is_numeric($id) || $id < 0) {
            throw new InvalidValueFormatException("El ID debe ser un entero mayor a 0");
        }
        $this->fields['id'] = (int) $id;
    }

    public function setMascotaId($id)
    {
        $this->fields['mascota_id'] = (int) $id;
    }

    public function setTipo(string $tipo)
    {
        $this->fields['tipo'] = $tipo;
    }

    public function setTitulo(string $titulo)
    {
        $this->fields['titulo'] = $titulo;
    }

    public function setFechaProgramada(string $fecha)
    {
        $this->fields['fecha_programada'] = $fecha;
    }

    public function setFechaRealizada(?string $fecha)
    {
        $this->fields['fecha_realizada'] = $fecha;
    }

    public function setEstado(string $estado)
    {
        $this->fields['estado'] = $estado;
    }

    public function setObservaciones(?string $obs)
    {
        $this->fields['observaciones'] = $obs;
    }

    public function setArchivoAdjunto(?string $archivo)
    {
        $this->fields['archivo_adjunto'] = $archivo;
    }

    public function setNotificado($notificado)
    {
        $this->fields['notificado'] = (bool)$notificado;
    }

    public function set(array $values)
    {
        foreach (array_keys($this->fields) as $field) {
            if (!array_key_exists($field, $values)) {
                continue;
            }
            // Convierte snake_case a camelCase para el setter
            $method = "set" . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            if (method_exists($this, $method)) {
                $this->$method($values[$field]);
            } else {
                $this->fields[$field] = $values[$field];
            }
        }
    }

    /**
     * Método para obtener el icono según el tipo
     */
    abstract public function getIconoHtml(): string;
}
