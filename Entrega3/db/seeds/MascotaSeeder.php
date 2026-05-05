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
            ],
            [
                'id' => 5,
                'refugio_id' => 2,
                'nombre' => 'Luna',
                'especie' => 'gato',
                'descripcion' => 'Gatita muy mimosa que busca compañía constante y muchos mimos.',
                'edad' => 2,
                'tamano' => 'pequeño',
                'temperamento' => 'cariñoso',
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado' => true,
                'castrado' => true,
                'imagen' => 'luna.jpg'
            ],
            [
                'id' => 6,
                'refugio_id' => 3,
                'nombre' => 'Max',
                'especie' => 'perro',
                'descripcion' => 'Cachorro mestizo lleno de energía, ideal para salir a correr o hacer senderismo.',
                'edad' => 1,
                'tamano' => 'mediano',
                'temperamento' => 'enérgico',
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado' => true,
                'castrado' => false,
                'imagen' => 'max.jpg'
            ],
            [
                'id' => 7,
                'refugio_id' => 1,
                'nombre' => 'Bella',
                'especie' => 'perro',
                'descripcion' => 'Perrita adulta muy dócil y obediente. Se lleva súper bien con otros animales.',
                'edad' => 5,
                'tamano' => 'grande',
                'temperamento' => 'tranquilo',
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado' => true,
                'castrado' => true,
                'imagen' => 'bella.jpg'
            ],
            [
                'id' => 8,
                'refugio_id' => 2,
                'nombre' => 'Simba',
                'especie' => 'gato',
                'descripcion' => 'Gato naranja independiente pero que disfruta de una buena siesta al sol en el balcón.',
                'edad' => 3,
                'tamano' => 'mediano',
                'temperamento' => 'independiente',
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado' => true,
                'castrado' => true,
                'imagen' => 'simba.jpg'
            ]
        ];

        $this->table('mascota')->insert($data)->saveData();
    }
}