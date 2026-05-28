<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFotoPerfilUsuarios extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('usuario');
        $table->addColumn('foto_perfil', 'string', ['limit' => 255, 'null' => true])
              ->update();
    }
}
