<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class MascotaSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['RefugioSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'refugio_id' => 1,
                'nombre' => 'Firulais',
                'especie' => 'perro',
                'descripcion' => 'Perro muy activo que busca una familia que le dé mucho amor y paseos.',
                'edad' => 2,
                'tamano' => 'mediano',
                'temperamento' => 'juguetón',
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado' => true,
                'castrado' => true,
                'imagen' => 'firulais.jpg'
            ],
            [
                'id' => 2,
                'refugio_id' => 1,
                'nombre' => 'Mishi',
                'especie' => 'gato',
                'descripcion' => 'Gata siamés muy dulce, ideal para departamentos.',
                'edad' => 1,
                'tamano' => 'pequeño',
                'temperamento' => 'tranquilo',
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado' => true,
                'castrado' => true,
                'imagen' => 'mishi.jpg'
            ],
            [
                'id' => 3,
                'refugio_id' => 2,
                'nombre' => 'Rocky',
                'especie' => 'perro',
                'descripcion' => 'Labrador muy noble, excelente con niños.',
                'edad' => 3,
                'tamano' => 'grande',
                'temperamento' => 'protector',
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado' => true,
                'castrado' => false,
                'imagen' => 'rocky.jpg'
            ],
            [
                'id' => 4,
                'refugio_id' => 3,
                'nombre' => 'Toby',
                'especie' => 'perro',
                'descripcion' => 'Beagle muy curioso, le encanta explorar el jardín.',
                'edad' => 1,
                'tamano' => 'pequeño',
                'temperamento' => 'curioso',
                'estado_adopcion' => 'ADOPTADO',
                'vacunado' => true,
                'castrado' => false,
                'imagen' => 'toby.jpg'
            ]
        ];

        $this->table('mascota')->insert($data)->saveData();
    }
}