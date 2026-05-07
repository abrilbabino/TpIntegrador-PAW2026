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
        // Truncate table first to avoid duplicate key errors
        $this->execute('TRUNCATE TABLE ubicacion RESTART IDENTITY CASCADE');

        $data = [
            [
                'refugio_id' => null,
                'latitud' => -34.5501,
                'longitud' => -59.1132,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => null,
                'latitud' => -34.5601,
                'longitud' => -59.1232,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => null,
                'latitud' => -34.6000,
                'longitud' => -58.5000,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => null,
                'latitud' => -34.5700,
                'longitud' => -58.7000,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 4,
                'latitud' => -34.5400,
                'longitud' => -58.9000,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 5,
                'latitud' => -34.5800,
                'longitud' => -59.0000,
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
                'latitud' => -34.5603,
                'longitud' => -59.0501,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 8,
                'latitud' => -34.5630,
                'longitud' => -59.0500,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 9,
                'latitud' => -34.5640,
                'longitud' => -59.0500,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'refugio_id' => 10,
                'latitud' => -34.5600,
                'longitud' => -59.0506,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
        ];

        $this->table('ubicacion')->insert($data)->saveData();
    }
}
