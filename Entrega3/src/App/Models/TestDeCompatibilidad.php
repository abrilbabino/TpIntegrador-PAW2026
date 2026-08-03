<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class TestDeCompatibilidad extends Model
{
    private array $respuestas = [];
    private ?string $resultado = null;

    public function setRespuestas(string $respuestas): void
    {
        $this->respuestas = json_decode($respuestas, true) ?? [];
    }

    public function getResultado(): ?string
    {
        return $this->resultado;
    }

    public function construirFiltrosBusqueda(): array
    {
        $filtros = ['estado_adopcion' => 'DISPONIBLE'];
        $r = $this->respuestas;
        
        $p1 = trim((string)($r['pregunta1'] ?? ''));
        $p2 = trim((string)($r['pregunta2'] ?? ''));
        $p3 = trim((string)($r['pregunta3'] ?? ''));
        $p5 = trim((string)($r['pregunta5'] ?? ''));

        if ($p5 === 'perro') {
            $filtros['especie'] = 'perro';
            $mensajeEspecie = 'perros';
        } elseif ($p5 === 'gato' || ($p5 === 'indiferente' && $p1 === 'departamento_chico' && $p2 === 'pocas')) {
            $filtros['especie'] = 'gato';
            $mensajeEspecie = 'gatos';
        } else {
            $mensajeEspecie = 'perros o gatos';
        }

        if (($filtros['especie'] ?? '') === 'perro') {
            if ($p1 === 'departamento_chico') {
                $filtros['tamano'] = ['pequeño'];
            } elseif ($p1 === 'departamento_grande') {
                $filtros['tamano'] = ($p3 === 'alta') ? ['mediano'] : ['pequeño', 'mediano'];
            }
        }

        if ($p3 === 'tranqui' || $p2 === 'pocas') {
            $filtros['temperamento'] = ['tranquilo', 'independiente'];
        } elseif ($p3 === 'alta') {
            $filtros['temperamento'] = ['enérgico', 'juguetón'];
        }
        
        $otrasMascotas = (array)($r['pregunta4'] ?? []);
        $tienePerro = in_array('perro', $otrasMascotas);
        $tieneGato = in_array('gato', $otrasMascotas);
        $mensajeExtra = "";

        if ($tienePerro || $tieneGato) {
            if (!isset($filtros['temperamento'])) {
                $filtros['temperamento'] = [];
            }
            
            if ($tienePerro && $tieneGato) {
                // Tiene Ambos
                $filtros['temperamento'][] = 'cariñoso';
                $mensajeExtra = " Al tener ya perros y gatos, priorizamos exclusivamente perfiles muy cariñosos para asegurar una buena convivencia en la manada.";
            } elseif ($tienePerro) {
                // Solo Perro
                $filtros['temperamento'][] = 'juguetón';
                $filtros['temperamento'][] = 'enérgico';
                $mensajeExtra = " Como ya tenés perro/s, priorizamos mascotas más juguetonas y enérgicas para que puedan jugar e interactuar a la par.";
            } elseif ($tieneGato) {
                // Solo Gato
                $filtros['temperamento'][] = 'tranquilo';
                $filtros['temperamento'][] = 'curioso';
                $mensajeExtra = " Al convivir con gatos, buscamos priorizar animales más tranquilos y curiosos para no estresarlos ni invadir su territorio.";
            }

            $filtros['temperamento'] = array_unique($filtros['temperamento']);
        }

        $this->resultado = json_encode([
            'tipo' => $filtros['especie'] ?? 'indiferente',
            'mensaje' => "Según tu espacio y rutina, te recomendamos buscar $mensajeEspecie con un nivel de energía acorde a tu estilo de vida." . $mensajeExtra
        ]);

        return $filtros;
    }
}
