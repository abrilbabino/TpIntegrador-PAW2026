<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeedDiccionarios extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO tamano (nombre) VALUES
            ('pequeño'), ('mediano'), ('grande')
            ON CONFLICT (nombre) DO NOTHING;
            
            INSERT INTO temperamento (nombre) VALUES
            ('tranquilo'), ('enérgico'), ('cariñoso'), ('independiente'), ('protector'), ('juguetón'), ('tímido'), ('curioso')
            ON CONFLICT (nombre) DO NOTHING;
        ");
    }
}
