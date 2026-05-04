<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class CalendarioSanitarioSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['MascotaSeeder', 'AdoptanteSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'mascota_id' => 1,
                'adoptante_id' => 1, 
                'tipo' => 'Vacunación',
                'fecha_programada' => date('Y-m-d', strtotime('-30 days')),
                'fecha_realizada' => date('Y-m-d', strtotime('-30 days')),
                'producto' => 'Vacuna Séxtuple',
                'notas' => 'Primera dosis aplicada en el refugio.',
                'estado' => 'COMPLETADO',
                'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-35 days'))
            ],
            [
                'mascota_id' => 2,
                'adoptante_id' => 2,
                'tipo' => 'Desparasitación',
                'fecha_programada' => date('Y-m-d', strtotime('+10 days')),
                'fecha_realizada' => null,
                'producto' => 'Pipeta Bravecto',
                'notas' => 'Recordar al adoptante comprar la pipeta mensual.',
                'estado' => 'PENDIENTE',
                'fecha_creacion' => date('Y-m-d H:i:s')
            ],
            [
                'mascota_id' => 4,
                'adoptante_id' => 3,
                'tipo' => 'Castración',
                'fecha_programada' => date('Y-m-d', strtotime('-5 days')), // Vencida
                'fecha_realizada' => null,
                'producto' => null,
                'notas' => 'Cláusula obligatoria a los 6 meses.',
                'estado' => 'PENDIENTE',
                'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-60 days'))
            ]
        ];

        $this->table('calendario_sanitario')->insert($data)->saveData();
    }
}