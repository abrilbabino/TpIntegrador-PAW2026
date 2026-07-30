<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeedDiccionarios extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO tamano (nombre) VALUES
            ('Pequeño'), ('Mediano'), ('Grande')
            ON CONFLICT (nombre) DO NOTHING;
            
            INSERT INTO temperamento (nombre) VALUES
            ('Tranquilo'), ('Enérgico'), ('Cariñoso'), ('Independiente'), ('Protector'), ('Juguetón'), ('Tímido'), ('Curioso')
            ON CONFLICT (nombre) DO NOTHING;
        ");
    }
}
