<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UbicacionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['RefugioSeeder'];
    }

    public function run(): void
    {
        // Limpiar tabla de forma segura (DELETE respeta ON DELETE SET_NULL en adoptante)
        $this->execute('DELETE FROM ubicacion');
        $this->execute("SELECT setval('ubicacion_id_seq', 1, false)");

        $data = [
            [
                'refugio_id' => null,
                'latitud' => -34.6515,
                'longitud' => -59.4307,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => null,
                'latitud' => -34.5703,
                'longitud' => -59.1050,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => null,
                'latitud' => -34.5800,
                'longitud' => -59.1100,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => null,
                'latitud' => -34.6300, // Medium spread NE
                'longitud' => -59.4100,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 4,
                'latitud' => -34.5650,
                'longitud' => -59.1150,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 5,
                'latitud' => -34.6700, // Medium spread SW
                'longitud' => -59.4500,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 6, 
                'latitud' => -34.5900, 
                'longitud' => -58.8000, 
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 7,
                'latitud' => -34.6500, 
                'longitud' => -59.4000,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 8,
                'latitud' => -34.6550, 
                'longitud' => -59.4600,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 9,
                'latitud' => -34.6350, 
                'longitud' => -59.4400,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 10,
                'latitud' => -34.6650, 
                'longitud' => -59.4200,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
        ];

        $this->table('ubicacion')->insert($data)->saveData();
    }
}
