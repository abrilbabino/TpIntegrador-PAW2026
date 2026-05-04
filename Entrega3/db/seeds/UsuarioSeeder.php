<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UsuarioSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            // Usuarios Adoptantes (IDs 1 al 3)
            [
                'id' => 1,
                'nombre_usuario' => 'juanperez',
                'email' => 'juan.perez@email.com',
                'contrasena' => password_hash('adoptante123', PASSWORD_DEFAULT),
                'contacto' => '11-2345-6789'
            ],
            [
                'id' => 2,
                'nombre_usuario' => 'mariagonzalez',
                'email' => 'maria.gonzalez@email.com',
                'contrasena' => password_hash('adoptante456', PASSWORD_DEFAULT),
                'contacto' => '11-3456-7890'
            ],
            [
                'id' => 3,
                'nombre_usuario' => 'carloslopez',
                'email' => 'carlos.lopez@email.com',
                'contrasena' => password_hash('adoptante789', PASSWORD_DEFAULT),
                'contacto' => '11-4567-8901'
            ],
            // Usuarios Refugios (IDs 4 al 6)
            [
                'id' => 4,
                'nombre_usuario' => 'patitas.felices',
                'email' => 'contacto@patitasfelices.org',
                'contrasena' => password_hash('refugio123', PASSWORD_DEFAULT),
                'contacto' => '0234-4567890'
            ],
            [
                'id' => 5,
                'nombre_usuario' => 'hogar.mercedes',
                'email' => 'info@hogarmercedes.org',
                'contrasena' => password_hash('refugio456', PASSWORD_DEFAULT),
                'contacto' => '0247-6543210'
            ],
            [
                'id' => 6,
                'nombre_usuario' => 'lujan.animal',
                'email' => 'adopciones@lujananimal.org',
                'contrasena' => password_hash('refugio789', PASSWORD_DEFAULT),
                'contacto' => '02323-123456'
            ]
        ];

        $this->table('usuario')->insert($data)->saveData();
    }
}