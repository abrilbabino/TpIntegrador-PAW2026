<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class RolRefugioSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->execute("
            UPDATE usuario
            SET rol = 'refugio'
            WHERE id IN (4, 5, 6)
        ");
    }
}