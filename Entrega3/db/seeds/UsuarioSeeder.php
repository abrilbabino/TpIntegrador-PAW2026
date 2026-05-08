<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UsuarioSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            // Usuarios Adoptantes
            [
                'nombre_usuario' => 'juanperez',
                'email' => 'juan.perez@email.com',
                'contrasena' => password_hash('adoptante123', PASSWORD_DEFAULT),
                'rol' => 'adoptante',
                'contacto' => '11-2345-6789'
            ],
            [
                'nombre_usuario' => 'mariagonzalez',
                'email' => 'maria.gonzalez@email.com',
                'contrasena' => password_hash('adoptante456', PASSWORD_DEFAULT),
                'rol' => 'adoptante',
                'contacto' => '11-3456-7890'
            ],
            [
                'nombre_usuario' => 'carloslopez',
                'email' => 'carlos.lopez@email.com',
                'contrasena' => password_hash('adoptante789', PASSWORD_DEFAULT),
                'rol' => 'adoptante',
                'contacto' => '11-4567-8901'
            ],
            // Usuarios Refugios
            [
                'nombre_usuario' => 'patitas.felices',
                'email' => 'contacto@patitasfelices.org',
                'contrasena' => password_hash('refugio123', PASSWORD_DEFAULT),
                'rol' => 'refugio',
                'contacto' => '0234-4567890'
            ],
            [
                'nombre_usuario' => 'hogar.mercedes',
                'email' => 'info@hogarmercedes.org',
                'contrasena' => password_hash('refugio456', PASSWORD_DEFAULT),
                'rol' => 'refugio',
                'contacto' => '0247-6543210'
            ],
            [
                'nombre_usuario' => 'lujan.animal',
                'email' => 'adopciones@lujananimal.org',
                'contrasena' => password_hash('refugio789', PASSWORD_DEFAULT),
                'rol' => 'refugio',
                'contacto' => '02323-123456'
            ],
            [
                'nombre_usuario' => 'Paw-Protection',
                'email' => 'PawProtection@lujananimal.org',
                'contrasena' => password_hash('refugio789', PASSWORD_DEFAULT),
                'rol' => 'refugio',
                'contacto' => '02323-123454'
            ],
            [
                'nombre_usuario' => 'Albergue.Dog',
                'email' => 'AlbergueDog@lujananimal.org',
                'contrasena' => password_hash('refugio789', PASSWORD_DEFAULT),
                'rol' => 'refugio',
                'contacto' => '02323-123453'
            ],
            [
                'nombre_usuario' => 'Amigos.Peludos',
                'email' => 'AmigosPeludos@lujananimal.org',
                'contrasena' => password_hash('refugio789', PASSWORD_DEFAULT),
                'rol' => 'refugio',
                'contacto' => '02323-123451'
            ],
            [
                'nombre_usuario' => 'Refugio-Esperanza',
                'email' => 'RefugioEsperanza@lujananimal.org',
                'contrasena' => password_hash('refugio789', PASSWORD_DEFAULT),
                'rol' => 'refugio',
                'contacto' => '02323-123452'
            ]
        ];

        $this->table('usuario')->insert($data)->saveData();
    }
}