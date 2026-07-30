<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablaColaEspera extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cola_espera');
        $table->addColumn('usuario_id', 'integer')
              ->addColumn('especie', 'string', ['null' => true])
              ->addColumn('raza', 'string', ['null' => true])
              ->addColumn('tamano', 'string', ['null' => true])
              ->addColumn('temperamento', 'string', ['null' => true])
              ->addColumn('provincia', 'string', ['null' => true])
              ->addColumn('ciudad', 'string', ['null' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('usuario_id', 'usuario', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
              ->create();
    }
}
