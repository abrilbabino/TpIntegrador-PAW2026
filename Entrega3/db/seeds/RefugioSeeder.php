<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class RefugioSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UsuarioSeeder'];
    }

    public function run(): void
    {
        $refugios = [
            [
                'id' => 1,
                'usuario_id' => 1,
                'nombre_institucion' => 'Refugio Patitas Felices',
                'cuit' => '30-12345678-9',
                'cvu' => null,
                'alias' => 'patitas_felices',
                'imagen' => 'refugioPatitas.jpg',
                'telefono' => '011-1234-5678'
            ],
            [
                'id' => 2,
                'usuario_id' => 2,
                'nombre_institucion' => 'Hogar Animal',
                'cuit' => '30-87654321-0',
                'cvu' => null,
                'alias' => 'hogar_animal',
                'imagen' => 'refugioHogar.jpg',
                'telefono' => '011-8765-4321'
            ],
            [
                'id' => 3,
                'usuario_id' => 3,
                'nombre_institucion' => 'SOS Mascotas',
                'cuit' => '30-11223344-5',
                'cvu' => null,
                'alias' => 'sos_mascotas',
                'imagen' => 'refugioSOS.jpg',
                'telefono' => '011-1122-3344'
            ],
            [
                'id' => 4,
                'usuario_id' => 1,
                'nombre_institucion' => 'Paw Protection',
                'cuit' => '30-55667788-1',
                'cvu' => null,
                'alias' => 'paw_protection',
                'imagen' => 'refugioPaw.jpg',
                'telefono' => '011-5566-7788'
            ],
            [
                'id' => 5,
                'usuario_id' => 2,
                'nombre_institucion' => 'Albergue Dog',
                'cuit' => '30-99887766-2',
                'cvu' => null,
                'alias' => 'albergue_dog',
                'imagen' => 'refugioAlbergue.jpg',
                'telefono' => '011-9988-7766'
            ],
            [
                'id' => 6,
                'usuario_id' => 3,
                'nombre_institucion' => 'Amigos Peludos',
                'cuit' => '30-22334455-3',
                'cvu' => null,
                'alias' => 'amigos_peludos',
                'imagen' => 'refugioAmigos.jpg',
                'telefono' => '011-2233-4455'
            ],
            [
                'id' => 7,
                'usuario_id' => 1,
                'nombre_institucion' => 'Refugio Esperanza',
                'cuit' => '30-66778899-4',
                'cvu' => null,
                'alias' => 'refugio_esperanza',
                'imagen' => 'refugioEsperanza.jpg',
                'telefono' => '011-6677-8899'
            ]
        ];

        $this->table('refugio')->insert($refugios)->saveData();
    }
}
