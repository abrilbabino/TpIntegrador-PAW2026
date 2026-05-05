<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class TestCompatibilidadSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['AdoptanteSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'adoptante_id' => 1,
                'respuestas' => json_encode([
                    'pregunta1' => 'departamento_chico',
                    'pregunta2' => 'pocas',
                    'pregunta3' => 'tranqui',
                    'pregunta4' => 'ninguno',
                    'pregunta5' => 'gato'
                ]),
                'resultado' => json_encode([
                    'mascotas_sugeridas' => [2, 5],
                    'mensaje' => 'Tu perfil es ideal para gatos independientes.'
                ])
            ],
            [
                'id' => 2,
                'adoptante_id' => 2,
                'respuestas' => json_encode([
                    'pregunta1' => 'casa_con_patio',
                    'pregunta2' => 'muchas',
                    'pregunta3' => 'alta',
                    'pregunta4' => 'perro',
                    'pregunta5' => 'perro'
                ]),
                'resultado' => json_encode([
                    'mascotas_sugeridas' => [1, 3, 4],
                    'mensaje' => 'Tu perfil es ideal para perros activos y grandes.'
                ])
            ],
            [
                'id' => 3,
                'adoptante_id' => 3,
                'respuestas' => json_encode([
                    'pregunta1' => 'departamento_grande',
                    'pregunta2' => 'mitad',
                    'pregunta3' => 'moderada',
                    'pregunta4' => 'gato',
                    'pregunta5' => 'indiferente'
                ]),
                'resultado' => json_encode([
                    'mascotas_sugeridas' => [1, 2, 3],
                    'mensaje' => 'Tu perfil es flexible, podés adoptar perros o gatos de energía moderada.'
                ])
            ]
        ];

        $this->table('test_de_compatibilidad')->insert($data)->saveData();
    }
}