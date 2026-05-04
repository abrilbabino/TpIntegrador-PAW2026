<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class RefugioSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UsuarioSeeder', 'UbicacionSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'usuario_id' => 4,
                'ubicacion_id' => 4,
                'nombre_institucion' => 'Refugio Patitas Felices',
                'cuit' => '30-12345678-9',
                'cvu' => '1234567890123456789012',
                'alias' => 'patitas.felices'
            ],
            [
                'id' => 2,
                'usuario_id' => 5,
                'ubicacion_id' => 5,
                'nombre_institucion' => 'Hogar de Mascotas Mercedes',
                'cuit' => '30-87654321-0',
                'cvu' => '0987654321098765432109',
                'alias' => 'hogar.mercedes'
            ],
            [
                'id' => 3,
                'usuario_id' => 6,
                'ubicacion_id' => 6,
                'nombre_institucion' => 'Refugio Luján Animal',
                'cuit' => '30-11223344-5',
                'cvu' => '1122334455667788990011',
                'alias' => 'lujan.animal'
            ]
        ];

        $this->table('refugio')->insert($data)->saveData();
    }
}