<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearDiccionariosMascota extends AbstractMigration
{
    public function up(): void
    {
        // 1. Tabla Especie
        $tableEspecie = $this->table('especie');
        $tableEspecie->addColumn('nombre', 'string', ['limit' => 50])
                     ->addIndex(['nombre'], ['unique' => true])
                     ->create();

        // 2. Tabla Tamano
        $tableTamano = $this->table('tamano');
        $tableTamano->addColumn('nombre', 'string', ['limit' => 50])
                    ->addIndex(['nombre'], ['unique' => true])
                    ->create();

        // 3. Tabla Temperamento
        $tableTemperamento = $this->table('temperamento');
        $tableTemperamento->addColumn('nombre', 'string', ['limit' => 100])
                          ->addIndex(['nombre'], ['unique' => true])
                          ->create();

        // Semillas por defecto
        $especies = [
            ['nombre' => 'Perro'],
            ['nombre' => 'Gato']
        ];
        $this->table('especie')->insert($especies)->save();
    }

    public function down(): void
    {
        $this->table('temperamento')->drop()->save();
        $this->table('tamano')->drop()->save();
        $this->table('especie')->drop()->save();
    }
}
