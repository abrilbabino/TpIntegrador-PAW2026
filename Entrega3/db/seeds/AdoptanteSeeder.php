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
        // Obtener IDs de usuarios adoptantes dinámicamente para las claves foráneas
        $usuarios = $this->fetchAll("SELECT id, nombre_usuario FROM usuario WHERE rol = 'adoptante' ORDER BY id ASC");

        // Mapear por nombre de usuario para mayor seguridad al asignar
        $userMap = [];
        foreach ($usuarios as $user) {
            $userMap[$user['nombre_usuario']] = $user['id'];
        }

        $data = [
            [
                'usuario_id' => $userMap['juanperez'],
                'ubicacion_id' => 1,
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'dni' => '12.345.678',
                'fecha_de_nacimiento' => '1990-05-15'
            ],
            [
                'usuario_id' => $userMap['mariagonzalez'],
                'ubicacion_id' => 2,
                'nombre' => 'María',
                'apellido' => 'González',
                'dni' => '23.456.789',
                'fecha_de_nacimiento' => '1985-08-22'
            ],
            [
                'usuario_id' => $userMap['carloslopez'],
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