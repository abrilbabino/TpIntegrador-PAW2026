<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class MediaMascotaSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['MascotaSeeder'];
    }

    public function run(): void
    {
        $data = [
            ['mascota_id' => 1, 'tipo' => 'video', 'url' => 'assets/videos/firulais.mp4'],

            ['mascota_id' => 2, 'tipo' => 'video', 'url' => 'assets/videos/mishi.mp4'],

            ['mascota_id' => 3, 'tipo' => 'video', 'url' => 'assets/videos/rocky.mp4'],

            ['mascota_id' => 4, 'tipo' => 'video', 'url' => 'assets/videos/toby.mp4'],

            ['mascota_id' => 5, 'tipo' => 'video', 'url' => 'assets/videos/luna.mp4'],

            ['mascota_id' => 5, 'tipo' => 'video', 'url' => 'assets/videos/luna.mp4'],

            ['mascota_id' => 6, 'tipo' => 'video', 'url' => 'assets/videos/max.mp4'],

            ['mascota_id' => 7, 'tipo' => 'video', 'url' => 'assets/videos/bella.mp4'],

            ['mascota_id' => 8, 'tipo' => 'video', 'url' => 'assets/videos/simba.mp4'],
        ];

        $this->table('media_mascota')->insert($data)->saveData();
    }
}