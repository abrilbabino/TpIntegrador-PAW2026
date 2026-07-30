<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RefactorizarMascotaYCola extends AbstractMigration
{
    public function up(): void
    {
        // 1. Agregar columnas a 'mascota'
        $tableMascota = $this->table('mascota');
        $tableMascota->addColumn('especie_id', 'integer', ['null' => true])
                     ->addColumn('tamano_id', 'integer', ['null' => true])
                     ->addColumn('temperamento_id', 'integer', ['null' => true])
                     ->update();

        // 2. MIGRAR DATOS EXISTENTES EN MASCOTA
        // Insertar especies únicas que no estén en la tabla diccionario (ignorando case)
        $this->execute("
            INSERT INTO especie (nombre)
            SELECT DISTINCT m.especie FROM mascota m
            WHERE m.especie IS NOT NULL AND m.especie != ''
            AND LOWER(m.especie) NOT IN (SELECT LOWER(nombre) FROM especie)
        ");

        // Insertar tamaños únicos
        $this->execute("
            INSERT INTO tamano (nombre)
            SELECT DISTINCT m.tamano FROM mascota m
            WHERE m.tamano IS NOT NULL AND m.tamano != ''
            AND LOWER(m.tamano) NOT IN (SELECT LOWER(nombre) FROM tamano)
        ");

        // Insertar temperamentos únicos
        $this->execute("
            INSERT INTO temperamento (nombre)
            SELECT DISTINCT m.temperamento FROM mascota m
            WHERE m.temperamento IS NOT NULL AND m.temperamento != ''
            AND LOWER(m.temperamento) NOT IN (SELECT LOWER(nombre) FROM temperamento)
        ");

        // Vincular los IDs en la tabla mascota (haciendo match case-insensitive)
        $this->execute("UPDATE mascota m SET especie_id = (SELECT id FROM especie e WHERE LOWER(e.nombre) = LOWER(m.especie)) WHERE m.especie IS NOT NULL AND m.especie != ''");
        $this->execute("UPDATE mascota m SET tamano_id = (SELECT id FROM tamano t WHERE LOWER(t.nombre) = LOWER(m.tamano)) WHERE m.tamano IS NOT NULL AND m.tamano != ''");
        $this->execute("UPDATE mascota m SET temperamento_id = (SELECT id FROM temperamento t WHERE LOWER(t.nombre) = LOWER(m.temperamento)) WHERE m.temperamento IS NOT NULL AND m.temperamento != ''");

        // Hacer las columnas NO NULL donde correspondía (especie en mascota era obligatoria)
        $tableMascota->changeColumn('especie_id', 'integer', ['null' => false])
                     ->addForeignKey('especie_id', 'especie', 'id', ['delete'=> 'RESTRICT', 'update'=> 'CASCADE'])
                     ->addForeignKey('tamano_id', 'tamano', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
                     ->addForeignKey('temperamento_id', 'temperamento', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
                     ->removeColumn('especie')
                     ->removeColumn('tamano')
                     ->removeColumn('temperamento')
                     ->update();

        // 3. Agregar columnas a 'cola_espera'

        $tableCola = $this->table('cola_espera');
        $tableCola->addColumn('especie_id', 'integer', ['null' => true])
                  ->addColumn('tamano_id', 'integer', ['null' => true])
                  ->addColumn('temperamento_id', 'integer', ['null' => true])
                  ->update();

        // Vincular IDs
        $this->execute("UPDATE cola_espera c SET especie_id = (SELECT id FROM especie e WHERE LOWER(e.nombre) = LOWER(c.especie)) WHERE c.especie IS NOT NULL AND c.especie != ''");
        $this->execute("UPDATE cola_espera c SET tamano_id = (SELECT id FROM tamano t WHERE LOWER(t.nombre) = LOWER(c.tamano)) WHERE c.tamano IS NOT NULL AND c.tamano != ''");
        $this->execute("UPDATE cola_espera c SET temperamento_id = (SELECT id FROM temperamento t WHERE LOWER(t.nombre) = LOWER(c.temperamento)) WHERE c.temperamento IS NOT NULL AND c.temperamento != ''");

        $tableCola->addForeignKey('especie_id', 'especie', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('tamano_id', 'tamano', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('temperamento_id', 'temperamento', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->removeColumn('especie')
                  ->removeColumn('tamano')
                  ->removeColumn('temperamento')
                  ->update();
    }

    public function down(): void
    {
        // Revertir Cola de Espera
        $tableCola = $this->table('cola_espera');
        $tableCola->addColumn('especie', 'string', ['limit' => 50, 'null' => true])
                  ->addColumn('tamano', 'string', ['limit' => 20, 'null' => true])
                  ->addColumn('temperamento', 'string', ['limit' => 100, 'null' => true])
                  ->update();
        
        $this->execute("UPDATE cola_espera c SET especie = (SELECT nombre FROM especie e WHERE e.id = c.especie_id) WHERE c.especie_id IS NOT NULL");
        $this->execute("UPDATE cola_espera c SET tamano = (SELECT nombre FROM tamano t WHERE t.id = c.tamano_id) WHERE c.tamano_id IS NOT NULL");
        $this->execute("UPDATE cola_espera c SET temperamento = (SELECT nombre FROM temperamento t WHERE t.id = c.temperamento_id) WHERE c.temperamento_id IS NOT NULL");

        $tableCola->dropForeignKey('especie_id')
                  ->dropForeignKey('tamano_id')
                  ->dropForeignKey('temperamento_id')
                  ->removeColumn('especie_id')
                  ->removeColumn('tamano_id')
                  ->removeColumn('temperamento_id')
                  ->update();

        // Revertir Mascota
        $tableMascota = $this->table('mascota');
        $tableMascota->addColumn('especie', 'string', ['limit' => 50, 'null' => false, 'default' => ''])
                     ->addColumn('tamano', 'string', ['limit' => 20, 'null' => true])
                     ->addColumn('temperamento', 'string', ['limit' => 100, 'null' => true])
                     ->update();

        $this->execute("UPDATE mascota m SET especie = (SELECT nombre FROM especie e WHERE e.id = m.especie_id) WHERE m.especie_id IS NOT NULL");
        $this->execute("UPDATE mascota m SET tamano = (SELECT nombre FROM tamano t WHERE t.id = m.tamano_id) WHERE m.tamano_id IS NOT NULL");
        $this->execute("UPDATE mascota m SET temperamento = (SELECT nombre FROM temperamento t WHERE t.id = m.temperamento_id) WHERE m.temperamento_id IS NOT NULL");

        $tableMascota->dropForeignKey('especie_id')
                     ->dropForeignKey('tamano_id')
                     ->dropForeignKey('temperamento_id')
                     ->removeColumn('especie_id')
                     ->removeColumn('tamano_id')
                     ->removeColumn('temperamento_id')
                     ->update();
    }
}
