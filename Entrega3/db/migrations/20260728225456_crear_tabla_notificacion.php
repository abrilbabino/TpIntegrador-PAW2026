<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablaNotificacion extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('notificacion');
        $table->addColumn('usuario_id', 'integer')
              ->addColumn('titulo', 'string', ['limit' => 255])
              ->addColumn('mensaje', 'text')
              ->addColumn('enlace', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('leida', 'boolean', ['default' => false])
              ->addColumn('fecha_creacion', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('usuario_id', 'usuario', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->create();
    }
}
