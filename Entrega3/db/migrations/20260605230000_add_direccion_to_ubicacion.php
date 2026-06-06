<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDireccionToUbicacion extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ubicacion');
        $table->addColumn('direccion', 'string', ['limit' => 255, 'null' => true])
              ->update();
    }
}
