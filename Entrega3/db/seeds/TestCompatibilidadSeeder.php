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
                    'tiene_casa' => 'si',
                    'tiene_patio' => 'si',
                    'otros_animales' => 'no'
                ]),
                'resultado' => json_encode([
                    'mascotas_sugeridas' => [1, 3],
                    'mensaje' => 'Tu perfil es ideal para perros medianos a grandes.'
                ])
            ],
            [
                'id' => 2,
                'adoptante_id' => 2,
                'respuestas' => json_encode([
                    'tiene_casa' => 'no',
                    'tiene_patio' => 'no',
                    'otros_animales' => 'no'
                ]),
                'resultado' => json_encode([
                    'mascotas_sugeridas' => [2],
                    'mensaje' => 'Tu perfil es ideal para gatos.'
                ])
            ]
        ];

        $this->table('test_de_compatibilidad')->insert($data)->saveData();
    }
}