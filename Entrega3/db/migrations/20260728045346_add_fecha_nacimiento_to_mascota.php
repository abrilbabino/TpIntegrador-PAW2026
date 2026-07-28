<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFechaNacimientoToMascota extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mascota');
        $table->addColumn('fecha_nacimiento', 'date', ['null' => true, 'after' => 'edad'])
              ->update();
    }
}
