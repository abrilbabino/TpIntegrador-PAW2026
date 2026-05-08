<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PawMapMigration extends AbstractMigration
{
    public function up(): void
    {
        $tableUsuario = $this->table('usuario');
        $tableUsuario->addColumn('nombre_usuario', 'string', ['limit' => 50])
                     ->addColumn('email', 'string', ['limit' => 100])
                     ->addColumn('contrasena', 'string', ['limit' => 255])
                     ->addColumn('rol', 'string', ['limit' => 30, 'default' => 'adoptante'])
                     ->addColumn('contacto', 'string', ['limit' => 100, 'null' => true])
                     ->addIndex(['nombre_usuario'], ['unique' => true])
                     ->addIndex(['email'], ['unique' => true])
                     ->create();

        $tableRefugio = $this->table('refugio', ['id' => false, 'primary_key' => 'usuario_id']);
        $tableRefugio->addColumn('usuario_id', 'integer', ['limit' => 11])
             ->addColumn('nombre_institucion', 'string', ['limit' => 150])
             ->addColumn('cuit', 'string', ['limit' => 20])
             ->addColumn('cvu', 'string', ['limit' => 50, 'null' => true])
             ->addColumn('alias', 'string', ['limit' => 50, 'null' => true])
             ->addColumn('imagen', 'string', ['limit' => 255, 'default' => 'default-refugio.jpg', 'null' => true])
             ->addColumn('telefono', 'string', ['limit' => 50, 'null' => true])
             // Definimos que usuario_id referencia a usuario(id)
             ->addForeignKey('usuario_id', 'usuario', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
             ->addIndex(['cuit'], ['unique' => true])
             ->create();

        $tableUbicacion = $this->table('ubicacion');
        $tableUbicacion->addColumn('refugio_id', 'integer', ['limit' => 11,'null' => true])
                        ->addColumn('latitud', 'decimal', ['precision' => 10, 'scale' => 8])
                        ->addColumn('longitud', 'decimal', ['precision' => 11, 'scale' => 8])
                        ->addColumn('ciudad', 'string', ['limit' => 100])
                        ->addColumn('provincia', 'string', ['limit' => 100])
                        ->addColumn('pais', 'string', ['limit' => 100])
                        ->addForeignKey('refugio_id', 'refugio', 'usuario_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                        ->create();

        $tableAdoptante = $this->table('adoptante',['id' => false, 'primary_key' => 'usuario_id']);
        $tableAdoptante->addColumn('usuario_id', 'integer', ['limit' => 11])
                       ->addColumn('ubicacion_id', 'integer', ['limit' => 11, 'null' => true])
                       ->addColumn('nombre', 'string', ['limit' => 100])
                       ->addColumn('apellido', 'string', ['limit' => 100])
                       ->addColumn('dni', 'string', ['limit' => 20, 'null' => true])
                       ->addColumn('fecha_de_nacimiento', 'date', ['null' => true])
                       ->addForeignKey('usuario_id', 'usuario', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                       ->addForeignKey('ubicacion_id', 'ubicacion', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
                       ->create();

        $tableMascota = $this->table('mascota');
        $tableMascota->addColumn('refugio_id', 'integer', ['limit' => 11])
                       ->addColumn('nombre', 'string', ['limit' => 100])
                       ->addColumn('especie', 'string', ['limit' => 50])
                       ->addColumn('descripcion', 'text', ['null' => true])
                       ->addColumn('edad', 'integer', ['null' => true])
                       ->addColumn('tamano', 'string', ['limit' => 20, 'null' => true])
                       ->addColumn('temperamento', 'string', ['limit' => 100, 'null' => true])
                       ->addColumn('estado_adopcion', 'string', ['limit' => 20, 'default' => 'DISPONIBLE'])
                       ->addColumn('vacunado', 'boolean', ['default' => false])
                       ->addColumn('castrado', 'boolean', ['default' => false])
                       ->addColumn('sexo', 'string', ['limit' => 10, 'default' => 'Desconocido'])
                       ->addColumn('imagen', 'string', ['limit' => 255, 'default' => 'default-pet.jpg'])
                       ->addForeignKey('refugio_id', 'refugio', 'usuario_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                       ->create();

        $tableMedia = $this->table('media_mascota');
        $tableMedia->addColumn('mascota_id', 'integer', ['limit' => 11])
                   ->addColumn('tipo', 'string', ['limit' => 20])
                   ->addColumn('url', 'string', ['limit' => 255])
                   ->addForeignKey('mascota_id', 'mascota', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                   ->create();

        $tableFavorito = $this->table('favorito');
        $tableFavorito->addColumn('adoptante_id', 'integer', ['limit' => 11])
                       ->addColumn('mascota_id', 'integer', ['limit' => 11])
                       ->addForeignKey('adoptante_id', 'adoptante', 'usuario_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                       ->addForeignKey('mascota_id', 'mascota', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                       ->create();

        $tableSolicitud = $this->table('solicitud_de_adopcion');
        $tableSolicitud->addColumn('adoptante_id', 'integer', ['limit' => 11])
                       ->addColumn('mascota_id', 'integer', ['limit' => 11])
                       ->addColumn('refugio_id', 'integer', ['limit' => 11])
                       ->addColumn('fecha', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                       ->addColumn('estado', 'string', ['limit' => 20, 'default' => 'PENDIENTE'])
                       ->addForeignKey('adoptante_id', 'adoptante', 'usuario_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                       ->addForeignKey('mascota_id', 'mascota', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                       ->addForeignKey('refugio_id', 'refugio', 'usuario_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                       ->create();

        $tableTest = $this->table('test_de_compatibilidad');
        $tableTest->addColumn('adoptante_id', 'integer', ['limit' => 11])
                  ->addColumn('respuestas', 'json')
                  ->addColumn('resultado', 'json', ['null' => true])
                  ->addForeignKey('adoptante_id', 'adoptante', 'usuario_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->create();

        $tableCalendario = $this->table('calendario_sanitario');
        $tableCalendario->addColumn('mascota_id', 'integer', ['limit' => 11])
                         ->addColumn('adoptante_id', 'integer', ['limit' => 11, 'null' => true])
                         ->addColumn('tipo', 'string', ['limit' => 50])
                         ->addColumn('fecha_programada', 'date')
                         ->addColumn('fecha_realizada', 'date', ['null' => true])
                         ->addColumn('producto', 'string', ['limit' => 100, 'null' => true])
                         ->addColumn('notas', 'text', ['null' => true])
                         ->addColumn('estado', 'string', ['limit' => 20, 'default' => 'PENDIENTE'])
                         ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                         ->addForeignKey('mascota_id', 'mascota', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                         ->addForeignKey('adoptante_id', 'adoptante', 'usuario_id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
                         ->create();
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS encuesta_adopcion CASCADE');
        $this->execute('DROP TABLE IF EXISTS calendario_sanitario CASCADE');
        $this->execute('DROP TABLE IF EXISTS test_de_compatibilidad CASCADE');
        $this->execute('DROP TABLE IF EXISTS solicitud_de_adopcion CASCADE');
        $this->execute('DROP TABLE IF EXISTS favorito CASCADE');
        $this->execute('DROP TABLE IF EXISTS media_mascota CASCADE');
        $this->execute('DROP TABLE IF EXISTS mascota CASCADE');
        $this->execute('DROP TABLE IF EXISTS ubicacion CASCADE');
        $this->execute('DROP TABLE IF EXISTS refugio CASCADE');
        $this->execute('DROP TABLE IF EXISTS adoptante CASCADE');
        $this->execute('DROP TABLE IF EXISTS usuario CASCADE');
    }
}
