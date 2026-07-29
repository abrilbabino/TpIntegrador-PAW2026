<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMascotasInvisiblesFields extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('mascota');
        $table->addColumn('visitas', 'integer', ['default' => 0, 'null' => false])
              ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
              ->update();
    }

    public function down(): void
    {
        $table = $this->table('mascota');
        $table->removeColumn('visitas')
              ->removeColumn('fecha_publicacion')
              ->update();
    }
}
