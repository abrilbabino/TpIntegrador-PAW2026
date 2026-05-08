<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CrearTablasTestCompatibilidad extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('test_compatibilidad_pregunta')) {
        $this->table('test_compatibilidad_pregunta')
            ->addColumn('nombre', 'string', ['limit' => 50])
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('orden', 'integer')
            ->create();
        }

        if (!$this->hasTable('test_compatibilidad_opcion')) {
            $this->table('test_compatibilidad_opcion')
                ->addColumn('pregunta_id', 'integer')
                ->addColumn('valor', 'string', ['limit' => 50])
                ->addColumn('etiqueta', 'string', ['limit' => 100])
                ->addColumn('subtitulo', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('orden', 'integer')
                ->addForeignKey('pregunta_id', 'test_compatibilidad_pregunta', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                ->create();
        }
    }

    public function down(): void
    {
        $this->table('test_compatibilidad_opcion')->drop()->save();
        $this->table('test_compatibilidad_pregunta')->drop()->save();
    }
}