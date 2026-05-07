<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRegistroSanitarioTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('registro_sanitario');
        $table->addColumn('mascota_id', 'integer')
              ->addColumn('tipo', 'string', ['limit' => 50])
              ->addColumn('titulo', 'string', ['limit' => 255])
              ->addColumn('fecha_programada', 'date')
              ->addColumn('fecha_realizada', 'date', ['null' => true])
              ->addColumn('estado', 'string', ['limit' => 20, 'default' => 'PENDIENTE'])
              ->addColumn('observaciones', 'text', ['null' => true])
              ->addForeignKey('mascota_id', 'mascota', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
              ->create();
    }
}
