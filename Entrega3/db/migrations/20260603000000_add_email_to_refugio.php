<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEmailToRefugio extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('refugio');
        $table->addColumn('email', 'string', ['limit' => 255, 'null' => true])
              ->update();
    }
}
