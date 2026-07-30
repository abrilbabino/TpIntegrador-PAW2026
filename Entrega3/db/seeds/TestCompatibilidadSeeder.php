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
        // Limpiar datos existentes
        $this->execute('DELETE FROM test_compatibilidad_opcion');
        $this->execute('DELETE FROM test_compatibilidad_pregunta');
        
        $preguntas = [
            [
                'nombre' => 'pregunta1',
                'titulo' => '¿Dónde vivís?',
                'orden' => 1,
                'opciones' => [
                    ['valor' => 'departamento_chico', 'etiqueta' => 'Departamento chico', 'subtitulo' => 'Monoambiente o 1 ambiente', 'orden' => 1, 'emoji' => '🏢'],
                    ['valor' => 'departamento_grande', 'etiqueta' => 'Departamento grande', 'subtitulo' => '2+ ambientes con balcón', 'orden' => 2, 'emoji' => '🏙️'],
                    ['valor' => 'casa_con_patio', 'etiqueta' => 'Casa con patio', 'subtitulo' => 'Patio o jardín', 'orden' => 3, 'emoji' => '🏡'],
                ]
            ],
            [
                'nombre' => 'pregunta2',
                'titulo' => '¿Cuántas horas pasás en casa por día?',
                'orden' => 2,
                'opciones' => [
                    ['valor' => 'pocas', 'etiqueta' => 'Pocas (menos de 8hs)', 'subtitulo' => 'Trabajo presencial full-time', 'orden' => 1, 'emoji' => '⏱️'],
                    ['valor' => 'mitad', 'etiqueta' => 'Mitad y mitad', 'subtitulo' => 'Híbrido o medio tiempo', 'orden' => 2, 'emoji' => '⚖️'],
                    ['valor' => 'muchas', 'etiqueta' => 'Muchas (8hs+)', 'subtitulo' => 'Home office o trabajo desde casa', 'orden' => 3, 'emoji' => '🏠'],
                ]
            ],
            [
                'nombre' => 'pregunta3',
                'titulo' => '¿Qué nivel de energía tenés?',
                'orden' => 3,
                'opciones' => [
                    ['valor' => 'tranqui', 'etiqueta' => 'Tranqui', 'subtitulo' => 'Prefiero paseos cortos y relax', 'orden' => 1, 'emoji' => '🧘'],
                    ['valor' => 'moderada', 'etiqueta' => 'Moderada', 'subtitulo' => 'Un par de paseos al día está bien', 'orden' => 2, 'emoji' => '🚶'],
                    ['valor' => 'alta', 'etiqueta' => 'Alta', 'subtitulo' => 'Salgo a correr/bici, soy muy activo', 'orden' => 3, 'emoji' => '🏃'],
                ]
            ],
            [
                'nombre' => 'pregunta4',
                'titulo' => '¿Tenés otras mascotas en casa?',
                'orden' => 4,
                'opciones' => [
                    ['valor' => 'perro', 'etiqueta' => 'Sí, perro/s', 'subtitulo' => '', 'orden' => 1, 'emoji' => '🐶'],
                    ['valor' => 'gato', 'etiqueta' => 'Sí, gato/s', 'subtitulo' => '', 'orden' => 2, 'emoji' => '🐱'],
                    ['valor' => 'ninguno', 'etiqueta' => 'No, sería el primero', 'subtitulo' => '', 'orden' => 3, 'emoji' => '✨'],
                ]
            ],
            [
                'nombre' => 'pregunta5',
                'titulo' => '¿Qué preferís?',
                'orden' => 5,
                'opciones' => [
                    ['valor' => 'perro', 'etiqueta' => 'Perro', 'subtitulo' => 'Compañero fiel, paseos, juego', 'orden' => 1, 'emoji' => '🐶'],
                    ['valor' => 'gato', 'etiqueta' => 'Gato', 'subtitulo' => 'Independiente, cariñoso, bajo mantenimiento', 'orden' => 2, 'emoji' => '🐱'],
                    ['valor' => 'indiferente', 'etiqueta' => '¡Ambos!', 'subtitulo' => 'Me gustan los dos por igual', 'orden' => 3, 'emoji' => '🐾'],
                ]
            ],
        ];

        $filaPregunta = [];
        $filaOpcion = [];
        
        foreach ($preguntas as $p) {
            // Insertar la pregunta y obtener su ID
            $this->execute("INSERT INTO test_compatibilidad_pregunta (nombre, titulo, orden) VALUES ('{$p['nombre']}', '{$p['titulo']}', {$p['orden']})");
            $preguntaId = $this->getAdapter()->getConnection()->lastInsertId();

            foreach ($p['opciones'] as $o) {
                $filaOpcion[] = [
                    'pregunta_id' => $preguntaId,
                    'valor' => $o['valor'],
                    'etiqueta' => $o['etiqueta'],
                    'subtitulo' => $o['subtitulo'],
                    'orden' => $o['orden'],
                    'emoji' => $o['emoji'] ?? '👉'
                ];
            }
        }

        $this->table('test_compatibilidad_opcion')->insert($filaOpcion)->saveData();
    }
}