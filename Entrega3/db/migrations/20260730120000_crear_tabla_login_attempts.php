<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablaLoginAttempts extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('login_attempts');
        $table->addColumn('username', 'string', ['limit' => 255])
              ->addColumn('intentos', 'integer', ['default' => 0])
              ->addColumn('bloqueado_hasta', 'datetime', ['null' => true])
              ->addIndex(['username'], ['unique' => true])
              ->create();
    }
}
