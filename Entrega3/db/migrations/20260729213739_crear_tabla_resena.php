<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablaResena extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('resena');
        $table->addColumn('adoptante_id', 'integer')
              ->addColumn('mascota_id', 'integer')
              ->addColumn('refugio_id', 'integer')
              ->addColumn('calificacion', 'integer')
              ->addColumn('comentario', 'text')
              ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('adoptante_id', 'usuario', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('mascota_id', 'mascota', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('refugio_id', 'usuario', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();
    }
}
