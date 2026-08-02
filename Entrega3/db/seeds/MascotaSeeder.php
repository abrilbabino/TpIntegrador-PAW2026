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
        // Resolver IDs de diccionarios dinámicamente, igual que RefugioSeeder con usuarios
        $especieMap      = [];
        $tamanoMap       = [];
        $temperamentoMap = [];

        foreach ($this->fetchAll("SELECT id, LOWER(nombre) as nombre FROM especie") as $row) {
            $especieMap[$row['nombre']] = $row['id'];
        }
        foreach ($this->fetchAll("SELECT id, LOWER(nombre) as nombre FROM tamano") as $row) {
            $tamanoMap[$row['nombre']] = $row['id'];
        }
        foreach ($this->fetchAll("SELECT id, LOWER(nombre) as nombre FROM temperamento") as $row) {
            $temperamentoMap[$row['nombre']] = $row['id'];
        }

        $data = [
            [
                'refugio_id'      => 4,
                'nombre'          => 'Firulais',
                'especie_id'      => $especieMap['perro'],
                'descripcion'     => 'Perro muy activo que busca una familia que le dé mucho amor y paseos.',
                'edad'            => 2,
                'tamano_id'       => $tamanoMap['mediano'],
                'temperamento_id' => $temperamentoMap['juguetón'],
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado'        => true,
                'castrado'        => true,
                'sexo'            => 'Macho',
                'imagen'          => 'firulais.jpg',
            ],
            [
                'refugio_id'      => 5,
                'nombre'          => 'Mishi',
                'especie_id'      => $especieMap['gato'],
                'descripcion'     => 'Gata siamés muy dulce, ideal para departamentos.',
                'edad'            => 1,
                'tamano_id'       => $tamanoMap['pequeño'],
                'temperamento_id' => $temperamentoMap['tranquilo'],
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado'        => true,
                'castrado'        => true,
                'sexo'            => 'Hembra',
                'imagen'          => 'mishi.jpg',
            ],
            [
                'refugio_id'      => 10,
                'nombre'          => 'Rocky',
                'especie_id'      => $especieMap['perro'],
                'descripcion'     => 'Labrador muy noble, excelente con niños.',
                'edad'            => 3,
                'tamano_id'       => $tamanoMap['grande'],
                'temperamento_id' => $temperamentoMap['protector'],
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado'        => true,
                'castrado'        => false,
                'sexo'            => 'Macho',
                'imagen'          => 'rocky.jpg',
            ],
            [
                'refugio_id'      => 9,
                'nombre'          => 'Toby',
                'especie_id'      => $especieMap['perro'],
                'descripcion'     => 'Beagle muy curioso, le encanta explorar el jardín.',
                'edad'            => 1,
                'tamano_id'       => $tamanoMap['pequeño'],
                'temperamento_id' => $temperamentoMap['curioso'],
                'estado_adopcion' => 'ADOPTADO',
                'vacunado'        => true,
                'castrado'        => false,
                'sexo'            => 'Macho',
                'imagen'          => 'toby.jpg',
            ],
            [
                'refugio_id'      => 6,
                'nombre'          => 'Luna',
                'especie_id'      => $especieMap['gato'],
                'descripcion'     => 'Gatita muy mimosa que busca compañía constante y muchos mimos.',
                'edad'            => 2,
                'tamano_id'       => $tamanoMap['pequeño'],
                'temperamento_id' => $temperamentoMap['cariñoso'],
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado'        => true,
                'castrado'        => true,
                'sexo'            => 'Hembra',
                'imagen'          => 'luna.jpg',
            ],
            [
                'refugio_id'      => 7,
                'nombre'          => 'Max',
                'especie_id'      => $especieMap['perro'],
                'descripcion'     => 'Cachorro mestizo lleno de energía, ideal para salir a correr o hacer senderismo.',
                'edad'            => 1,
                'tamano_id'       => $tamanoMap['mediano'],
                'temperamento_id' => $temperamentoMap['enérgico'],
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado'        => true,
                'castrado'        => false,
                'sexo'            => 'Macho',
                'imagen'          => 'max.jpg',
            ],
            [
                'refugio_id'      => 7,
                'nombre'          => 'Bella',
                'especie_id'      => $especieMap['perro'],
                'descripcion'     => 'Perrita adulta muy dócil y obediente. Se lleva súper bien con otros animales.',
                'edad'            => 5,
                'tamano_id'       => $tamanoMap['grande'],
                'temperamento_id' => $temperamentoMap['tranquilo'],
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado'        => true,
                'castrado'        => true,
                'sexo'            => 'Hembra',
                'imagen'          => 'bella.jpg',
            ],
            [
                'refugio_id'      => 8,
                'nombre'          => 'Simba',
                'especie_id'      => $especieMap['gato'],
                'descripcion'     => 'Gato naranja independiente pero que disfruta de una buena siesta al sol en el balcón.',
                'edad'            => 3,
                'tamano_id'       => $tamanoMap['mediano'],
                'temperamento_id' => $temperamentoMap['independiente'],
                'estado_adopcion' => 'DISPONIBLE',
                'vacunado'        => true,
                'castrado'        => true,
                'sexo'            => 'Macho',
                'imagen'          => 'simba.jpg',
            ],
        ];

        $this->table('mascota')->insert($data)->saveData();
    }
}