<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddContratoToSolicitudAdopcion extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('solicitud_de_adopcion');
        $table->addColumn('contrato_aceptado', 'boolean', ['default' => false])
              ->addColumn('fecha_aceptacion', 'timestamp', ['null' => true])
              ->update();
    }
}
