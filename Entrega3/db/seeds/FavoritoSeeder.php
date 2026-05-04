<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class FavoritoSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['AdoptanteSeeder', 'MascotaSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'adoptante_id' => 1,
                'mascota_id' => 1
            ],
            [
                'id' => 2,
                'adoptante_id' => 1,
                'mascota_id' => 3
            ],
            [
                'id' => 3,
                'adoptante_id' => 2,
                'mascota_id' => 2
            ]
        ];

        $this->table('favorito')->insert($data)->saveData();
    }
}