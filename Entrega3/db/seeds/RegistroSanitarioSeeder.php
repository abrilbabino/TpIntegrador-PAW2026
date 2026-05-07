<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class RegistroSanitarioSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                'mascota_id'       => 1,
                'tipo'             => 'vacuna',
                'titulo'           => 'Vacuna Antirrábica',
                'fecha_programada' => '2025-01-10',
                'fecha_realizada'  => '2025-01-12',
                'estado'           => 'COMPLETADO',
                'observaciones'    => 'Sin reacciones adversas.',
            ],
            [
                'mascota_id'       => 1,
                'tipo'             => 'cirugia',
                'titulo'           => 'Castración',
                'fecha_programada' => '2025-03-15',
                'fecha_realizada'  => '2025-03-15',
                'estado'           => 'COMPLETADO',
                'observaciones'    => 'Recuperación exitosa.',
            ],
            [
                'mascota_id'       => 1,
                'tipo'             => 'chequeo',
                'titulo'           => 'Chequeo General Anual',
                'fecha_programada' => '2025-06-20',
                'fecha_realizada'  => '2025-06-22',
                'estado'           => 'COMPLETADO',
                'observaciones'    => 'Peso ideal, buena salud.',
            ],
            [
                'mascota_id'       => 1,
                'tipo'             => 'desparasitacion',
                'titulo'           => 'Desparasitación Interna',
                'fecha_programada' => date('Y-m-d', strtotime('+1 month')),
                'fecha_realizada'  => null,
                'estado'           => 'PENDIENTE',
                'observaciones'    => 'Traer muestra de heces si es posible.',
            ]
        ];

        $table = $this->table('registro_sanitario');
        $table->insert($data)->saveData();
    }
}
