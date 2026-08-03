<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEtiquetasToMascota extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mascota');
        $table->addColumn('ideal_depto',    'boolean', ['default' => false, 'null' => false])
              ->addColumn('convive_perros',  'boolean', ['default' => false, 'null' => false])
              ->addColumn('convive_gatos',   'boolean', ['default' => false, 'null' => false])
              ->addColumn('apto_ninos',      'boolean', ['default' => false, 'null' => false])
              ->update();
    }
}
