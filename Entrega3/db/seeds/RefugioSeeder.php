<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class RefugioSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UsuarioSeeder'];
    }

    public function run(): void
    {
        // Obtener IDs de usuarios refugio dinámicamente
        $usuarios = $this->fetchAll("SELECT id, nombre_usuario FROM usuario WHERE rol = 'refugio'");
        
        $userMap = [];
        foreach ($usuarios as $user) {
            $userMap[$user['nombre_usuario']] = $user['id'];
        }

        $refugios = [
            [
                'usuario_id' => $userMap['patitas.felices'],
                'nombre_institucion' => 'Refugio Patitas Felices',
                'cuit' => '30-12345678-9',
                'cvu' => null,
                'alias' => 'patitas_felices',
                'imagen' => 'refugioPatitas.jpg',
                'telefono' => '011-1234-5678',
                'email' => 'contacto@patitasfelices.org',
                'descripcion' => 'Somos una ONG dedicada al rescate, recuperación y reubicación de animales en situación de calle o maltrato. Nuestro objetivo es brindarles una segunda oportunidad a aquellos que más lo necesitan, asegurando su bienestar físico y emocional antes de darlos en adopción.'
            ],
            [
                'usuario_id' => $userMap['hogar.mercedes'],
                'nombre_institucion' => 'Hogar Animal',
                'cuit' => '30-87654321-0',
                'cvu' => null,
                'alias' => 'hogar_animal',
                'imagen' => 'refugioHogar.jpg',
                'telefono' => '011-8765-4321',
                'email' => 'adopciones@hogaranimal.com.ar',
                'descripcion' => 'En Hogar Animal trabajamos día a día para rescatar mascotas abandonadas y encontrarles familias amorosas. Contamos con un equipo de voluntarios comprometidos que cuidan a cada animalito como si fuera propio.'
            ],
            [
                'usuario_id' => $userMap['lujan.animal'],
                'nombre_institucion' => 'SOS Mascotas',
                'cuit' => '30-11223344-5',
                'cvu' => null,
                'alias' => 'sos_mascotas',
                'imagen' => 'refugioSOS.jpg',
                'telefono' => '011-1122-3344',
                'email' => 'info@sosmascotas.org.ar',
                'descripcion' => 'Un refugio creado por vecinos unidos por el amor a los animales. Nos especializamos en la rehabilitación de perros y gatos rescatados de situaciones extremas, dándoles el amor y cuidado veterinario que merecen.'
            ],
            [
                'usuario_id' => $userMap['Paw-Protection'],
                'nombre_institucion' => 'Paw Protection',
                'cuit' => '30-55667788-1',
                'cvu' => null,
                'alias' => 'paw_protection',
                'imagen' => 'refugioPaw.jpg',
                'telefono' => '011-5566-7788',
                'email' => 'hello@pawprotection.org',
                'descripcion' => 'Dedicados a la protección animal desde hace más de 10 años. Promovemos la adopción responsable, la esterilización y concientizamos sobre el cuidado y respeto hacia todas las especies de compañía.'
            ],
            [
                'usuario_id' => $userMap['Albergue.Dog'],
                'nombre_institucion' => 'Albergue Dog',
                'cuit' => '30-99887766-2',
                'cvu' => null,
                'alias' => 'albergue_dog',
                'imagen' => 'refugioAlbergue.jpg',
                'telefono' => '011-9988-7766',
                'email' => 'contacto@alberguedog.com',
                'descripcion' => 'Un albergue de tránsito enfocado en dar asilo temporal a perros rescatados. Nuestro equipo evalúa el comportamiento y la salud de cada perro para asegurar que la familia adoptante sea el match perfecto.'
            ],
            [
                'usuario_id' => $userMap['Amigos.Peludos'],
                'nombre_institucion' => 'Amigos Peludos',
                'cuit' => '30-22334455-3',
                'cvu' => null,
                'alias' => 'amigos_peludos',
                'imagen' => 'refugioAmigos.jpg',
                'telefono' => '011-2233-4455',
                'email' => 'hola@amigospeludos.ar',
                'descripcion' => 'Somos un pequeño refugio familiar que acoge animales en estado vulnerable. Nos aseguramos de que cada perro o gato se vaya castrado, vacunado y lleno de amor a su nuevo hogar definitivo.'
            ],
            [
                'usuario_id' => $userMap['Refugio-Esperanza'],
                'nombre_institucion' => 'Refugio Esperanza',
                'cuit' => '30-66778899-4',
                'cvu' => null,
                'alias' => 'refugio_esperanza',
                'imagen' => 'refugioEsperanza.jpg',
                'telefono' => '011-6677-8899',
                'email' => 'refugioesperanza@gmail.com',
                'descripcion' => 'Un refugio a puertas abiertas que invita a las familias a conocer a los animales en su entorno natural. Creemos que la conexión sincera entre un humano y un animal es el primer paso hacia una adopción exitosa.'
            ]
        ];

        $table = $this->table('refugio');
        
        foreach ($refugios as $ref) {
            $exists = $this->fetchRow("SELECT 1 FROM refugio WHERE usuario_id = " . $ref['usuario_id']);
            if (empty($exists)) {
                $table->insert($ref);
            } else {
                $stmt = $this->getAdapter()->getConnection()->prepare("UPDATE refugio SET descripcion = :descripcion, email = :email WHERE usuario_id = :id");
                $stmt->execute([
                    ':descripcion' => $ref['descripcion'],
                    ':email' => $ref['email'],
                    ':id' => $ref['usuario_id']
                ]);
            }
        }
        
        $table->saveData();
    }
}
