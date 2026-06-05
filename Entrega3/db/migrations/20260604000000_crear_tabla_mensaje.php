<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablaMensaje extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('mensaje');
        $table->addColumn('solicitud_id', 'integer', ['limit' => 11])
              ->addColumn('remitente_id', 'integer', ['limit' => 11])
              ->addColumn('destinatario_id', 'integer', ['limit' => 11])
              ->addColumn('contenido', 'text')
              ->addColumn('leido', 'boolean', ['default' => false])
              ->addColumn('fecha_envio', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('solicitud_id', 'solicitud_de_adopcion', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('remitente_id', 'usuario', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('destinatario_id', 'usuario', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();
    }
}
