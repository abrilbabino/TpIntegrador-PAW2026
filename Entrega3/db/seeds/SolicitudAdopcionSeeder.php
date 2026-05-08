<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SolicitudAdopcionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['AdoptanteSeeder', 'MascotaSeeder', 'RefugioSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'adoptante_id' => 1,
                'mascota_id' => 1,
                'refugio_id' => 4,
                'estado' => 'PENDIENTE',
                'fecha' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'adoptante_id' => 2,
                'mascota_id' => 2,
                'refugio_id' => 5,
                'estado' => 'APROBADA',
                'fecha' => date('Y-m-d H:i:s', strtotime('-10 days'))
            ],
            [
                'adoptante_id' => 3,
                'mascota_id' => 4,
                'refugio_id' => 9,
                'estado' => 'APROBADA',
                'fecha' => date('Y-m-d H:i:s', strtotime('-2 months'))
            ]
        ];

        $this->table('solicitud_de_adopcion')->insert($data)->saveData();
    }
}