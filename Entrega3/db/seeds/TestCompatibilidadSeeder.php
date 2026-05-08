<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class TestCompatibilidadSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        $preguntas = [
            [
                'nombre' => 'pregunta1',
                'titulo' => '¿Dónde vivís?',
                'orden' => 1,
                'opciones' => [
                    ['valor' => 'departamento_chico', 'etiqueta' => 'Departamento chico', 'subtitulo' => 'Monoambiente o 1 ambiente', 'orden' => 1],
                    ['valor' => 'departamento_grande', 'etiqueta' => 'Departamento grande', 'subtitulo' => '2+ ambientes con balcón', 'orden' => 2],
                    ['valor' => 'casa_con_patio', 'etiqueta' => 'Casa con patio', 'subtitulo' => 'Patio o jardín', 'orden' => 3],
                ]
            ],
            [
                'nombre' => 'pregunta2',
                'titulo' => '¿Cuántas horas pasás en casa por día?',
                'orden' => 2,
                'opciones' => [
                    ['valor' => 'pocas', 'etiqueta' => 'Pocas (menos de 8hs)', 'subtitulo' => 'Trabajo presencial full-time', 'orden' => 1],
                    ['valor' => 'mitad', 'etiqueta' => 'Mitad y mitad', 'subtitulo' => 'Híbrido o medio tiempo', 'orden' => 2],
                    ['valor' => 'muchas', 'etiqueta' => 'Muchas (8hs+)', 'subtitulo' => 'Home office o trabajo desde casa', 'orden' => 3],
                ]
            ],
            [
                'nombre' => 'pregunta3',
                'titulo' => '¿Qué nivel de energía tenés?',
                'orden' => 3,
                'opciones' => [
                    ['valor' => 'tranqui', 'etiqueta' => 'Tranqui', 'subtitulo' => 'Prefiero paseos cortos y relax', 'orden' => 1],
                    ['valor' => 'moderada', 'etiqueta' => 'Moderada', 'subtitulo' => 'Un par de paseos al día está bien', 'orden' => 2],
                    ['valor' => 'alta', 'etiqueta' => 'Alta', 'subtitulo' => 'Salgo a correr/bici, soy muy activo', 'orden' => 3],
                ]
            ],
            [
                'nombre' => 'pregunta4',
                'titulo' => '¿Tenés otras mascotas en casa?',
                'orden' => 4,
                'opciones' => [
                    ['valor' => 'perro', 'etiqueta' => 'Sí, perro/s', 'subtitulo' => '', 'orden' => 1],
                    ['valor' => 'gato', 'etiqueta' => 'Sí, gato/s', 'subtitulo' => '', 'orden' => 2],
                    ['valor' => 'ninguno', 'etiqueta' => 'No, sería el primero', 'subtitulo' => '', 'orden' => 3],
                ]
            ],
            [
                'nombre' => 'pregunta5',
                'titulo' => '¿Qué preferís?',
                'orden' => 5,
                'opciones' => [
                    ['valor' => 'perro', 'etiqueta' => 'Perro', 'subtitulo' => 'Compañero fiel, paseos, juego', 'orden' => 1],
                    ['valor' => 'gato', 'etiqueta' => 'Gato', 'subtitulo' => 'Independiente, cariñoso, bajo mantenimiento', 'orden' => 2],
                    ['valor' => 'indiferente', 'etiqueta' => 'Me da igual', 'subtitulo' => 'Estoy abierto a lo que mejor se adapte', 'orden' => 3],
                ]
            ],
        ];

        $filaPregunta = [];
        $filaOpcion = [];
        $pid = 1;
        foreach ($preguntas as $p) {
            $filaPregunta[] = [
                'id' => $pid,
                'nombre' => $p['nombre'],
                'titulo' => $p['titulo'],
                'orden' => $p['orden'],
            ];
            foreach ($p['opciones'] as $o) {
                $filaOpcion[] = [
                    'pregunta_id' => $pid,
                    'valor' => $o['valor'],
                    'etiqueta' => $o['etiqueta'],
                    'subtitulo' => $o['subtitulo'],
                    'orden' => $o['orden'],
                ];
            }
            $pid++;
        }

        $this->table('test_compatibilidad_pregunta')->insert($filaPregunta)->saveData();
        $this->table('test_compatibilidad_opcion')->insert($filaOpcion)->saveData();
    }
}