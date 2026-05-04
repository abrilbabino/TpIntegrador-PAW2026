<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UbicacionSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'latitud' => -34.6500,
                'longitud' => -59.4300,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'id' => 2,
                'latitud' => -34.5702,
                'longitud' => -59.1050,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'id' => 3,
                'latitud' => -34.6515,
                'longitud' => -59.4320,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'id' => 4,
                'latitud' => -34.5167,
                'longitud' => -58.7667,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'id' => 5,
                'latitud' => -34.6614,
                'longitud' => -59.4300,
                'ciudad' => 'Mercedes',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ],
            [
                'id' => 6,
                'latitud' => -34.6714,
                'longitud' => -59.4400,
                'ciudad' => 'Luján',
                'provincia' => 'Buenos Aires',
                'pais' => 'Argentina'
            ]
        ];

        $this->table('ubicacion')->insert($data)->saveData();
    }
}