<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEmojiToTestOpciones extends AbstractMigration
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
        $table = $this->table('test_compatibilidad_opcion');
        $table->addColumn('emoji', 'string', [
            'limit' => 20,
            'null' => true,
            'default' => '👉',
            'comment' => 'Emoji visual para la opcion en el frontend'
        ])
        ->update();
    }
}
