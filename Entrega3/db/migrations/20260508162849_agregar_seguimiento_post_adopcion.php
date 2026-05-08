<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AgregarSeguimientoPostAdopcion extends AbstractMigration
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
        // 1. Modificar registro_sanitario
        $tablaRegistro = $this->table('registro_sanitario');
        $tablaRegistro->addColumn('archivo_adjunto', 'string', ['limit' => 255, 'null' => true])
                      ->addColumn('notificado', 'boolean', ['default' => false, 'null' => true])
                      ->update();

        $tablaEncuesta = $this->table('encuesta_adopcion');
        $tablaEncuesta->addColumn('mascota_id', 'integer')
                      ->addColumn('adoptante_id', 'integer')
                      ->addColumn('fecha_encuesta', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                      ->addColumn('etapa', 'string', ['limit' => 20, 'default' => 'inicial'])
                      ->addColumn('progreso_general', 'text', ['null' => true])
                      ->addColumn('conducta', 'string', ['limit' => 100, 'null' => true])
                      ->addColumn('sueno', 'string', ['limit' => 100, 'null' => true])
                      ->addColumn('alimentacion', 'string', ['limit' => 100, 'null' => true])
                      ->addColumn('comentarios', 'text', ['null' => true])
                      ->addColumn('alerta_generada', 'boolean', ['default' => false])
                      ->addForeignKey('mascota_id', 'mascota', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
                      ->create();
    }
}
