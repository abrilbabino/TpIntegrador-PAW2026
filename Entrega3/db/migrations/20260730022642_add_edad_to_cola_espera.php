<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEdadToColaEspera extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cola_espera');
        $table->addColumn('edad_min', 'integer', ['null' => true])
              ->addColumn('edad_max', 'integer', ['null' => true])
              ->update();
    }
}
