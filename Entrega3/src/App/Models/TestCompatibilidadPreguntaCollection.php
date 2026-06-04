<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class TestCompatibilidadPreguntaCollection extends Model
{
    public function getAll(): array
    {
        $sql = "
            SELECT p.nombre AS pregunta_nombre, p.titulo AS pregunta_titulo, o.valor, o.etiqueta, o.subtitulo, o.emoji 
            FROM test_compatibilidad_pregunta p
            LEFT JOIN test_compatibilidad_opcion o ON p.id = o.pregunta_id
            ORDER BY p.orden, o.orden
        ";

        $rows = $this->queryBuilder->rawQuery($sql);

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

