<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class TestCompatibilidadPreguntaCollection extends Model
{
    public function getAll(): array
    {
        $rows = $this->queryBuilder->obtenerPreguntasTestCompatibilidad();

        $preguntas = [];

        foreach ($rows as $row) {
            $name = $row['pregunta_nombre'];

            if (!isset($preguntas[$name])) {
                $preguntas[$name] = [
                    'name' => $name,
                    'titulo' => $row['pregunta_titulo'],
                    'opciones' => []
                ];
            }

            if ($row['valor']) { // Por si una pregunta no tiene opciones
                $preguntas[$name]['opciones'][] = [
                    'valor' => $row['valor'],
                    'etiqueta' => $row['etiqueta'],
                    'subtitulo' => $row['subtitulo'],
                    'emoji' => $row['emoji']
                ];
            }
        }

        return array_values($preguntas);
    }
}

