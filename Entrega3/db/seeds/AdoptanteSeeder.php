<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AdoptanteSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UsuarioSeeder', 'UbicacionSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'usuario_id' => 1,
                'ubicacion_id' => 1,
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'dni' => '12.345.678',
                'fecha_de_nacimiento' => '1990-05-15'
            ],
            [
                'usuario_id' => 2,
                'ubicacion_id' => 2,
                'nombre' => 'María',
                'apellido' => 'González',
                'dni' => '23.456.789',
                'fecha_de_nacimiento' => '1985-08-22'
            ],
            [
                'usuario_id' => 3,
                'ubicacion_id' => 3,
                'nombre' => 'Carlos',
                'apellido' => 'López',
                'dni' => '34.567.890',
                'fecha_de_nacimiento' => '1992-12-10'
            ]
        ];

        $this->table('adoptante')->insert($data)->saveData();
    }
}