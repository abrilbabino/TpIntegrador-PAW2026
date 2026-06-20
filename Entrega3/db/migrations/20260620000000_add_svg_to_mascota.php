<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSvgToMascota extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mascota');
        $table->addColumn('svg', 'string', ['limit' => 100, 'null' => true, 'default' => null])
              ->update();
    }
}
