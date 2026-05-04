<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class MediaMascotaSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['MascotaSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'mascota_id' => 1,
                'tipo' => 'foto',
                'url' => 'uploads/mascotas/firulais_1.jpg'
            ],
            [
                'id' => 2,
                'mascota_id' => 2,
                'tipo' => 'foto',
                'url' => 'uploads/mascotas/mishi_1.jpg'
            ],
            [
                'id' => 3,
                'mascota_id' => 3,
                'tipo' => 'foto',
                'url' => 'uploads/mascotas/rocky_1.jpg'
            ],
            [
                'id' => 4,
                'mascota_id' => 4,
                'tipo' => 'foto',
                'url' => 'uploads/mascotas/toby_1.jpg'
            ]
        ];

        $this->table('media_mascota')->insert($data)->saveData();
    }
}