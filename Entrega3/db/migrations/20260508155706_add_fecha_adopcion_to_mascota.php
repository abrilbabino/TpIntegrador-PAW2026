<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFechaAdopcionToMascota extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mascota');
        $table->addColumn('fecha_adopcion', 'datetime', ['null' => true])
              ->update();
    }
}
