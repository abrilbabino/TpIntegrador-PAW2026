<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LimpiarDuplicadosDiccionarios extends AbstractMigration
{
    public function up(): void
    {
        // 1. Aseguramos que existan las versiones en minúscula
        $this->execute(" INSERT INTO tamano (nombre) VALUES ('pequeño'), ('mediano'), ('grande') ON CONFLICT (nombre) DO NOTHING;
            INSERT INTO especie (nombre) VALUES ('perro'), ('gato') ON CONFLICT (nombre) DO NOTHING;
            INSERT INTO temperamento (nombre) VALUES  ('tranquilo'), ('enérgico'), ('cariñoso'), ('independiente'), ('protector'), ('juguetón'), ('tímido'), ('curioso')
            ON CONFLICT (nombre) DO NOTHING;
        ");

        // 2. Redirigimos todas las FKs de versiones con mayúscula hacia la versión minúscula
        $diccionarios = [
            'tamano' => ['pequeño', 'mediano', 'grande'],
            'especie' => ['perro', 'gato'],
            'temperamento' => ['tranquilo', 'enérgico', 'cariñoso', 'independiente', 'protector', 'juguetón', 'tímido', 'curioso']
        ];

        foreach ($diccionarios as $tabla => $valores) {
            foreach ($valores as $valorMinuscula) {
                // Actualizar FK en tabla mascota
                $this->execute(" UPDATE mascota SET {$tabla}_id = (SELECT id FROM {$tabla} WHERE nombre = '{$valorMinuscula}')
                    WHERE {$tabla}_id IN (SELECT id FROM {$tabla} WHERE LOWER(nombre) = '{$valorMinuscula}' AND nombre != '{$valorMinuscula}'
                    )
                ");
                // Actualizar FK en tabla cola_espera
                $this->execute("UPDATE cola_espera SET {$tabla}_id = (SELECT id FROM {$tabla} WHERE nombre = '{$valorMinuscula}')
                    WHERE {$tabla}_id IN (SELECT id FROM {$tabla} WHERE LOWER(nombre) = '{$valorMinuscula}' AND nombre != '{$valorMinuscula}'
                    )
                ");
            }

            // 3. Borramos cualquier registro en el diccionario que no sea todo minúscula
            $this->execute("DELETE FROM {$tabla} WHERE nombre != LOWER(nombre)
            ");
        }
    }

    public function down(): void
    {
        // No hay rollback
    }
}
