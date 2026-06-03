<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDescripcionToRefugio extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('refugio');
        $table->addColumn('descripcion', 'text', ['null' => true])
              ->update();
    }
}
