<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class EncuestaAdopcionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['SolicitudAdopcionSeeder', 'AdoptanteSeeder', 'MascotaSeeder'];
    }

    public function run(): void
    {
        $data = [
            [
                'solicitud_id' => 2,
                'adoptante_id' => 2,
                'mascota_id' => 2,
                'fecha_encuesta' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'preguntas_respuestas' => json_encode([
                    'alimentacion' => 'Come excelente, ración completa.',
                    'sueno' => 'Duerme toda la noche en su cama.',
                    'conducta' => 'Muy tranquila, usa las piedritas sin problema.'
                ]),
                'puntuacion' => 5,
                'comentarios' => 'Estamos muy felices con Mishi.',
                'necesita_seguimiento' => false
            ],
            [
                'solicitud_id' => 3,
                'adoptante_id' => 3,
                'mascota_id' => 4,
                'fecha_encuesta' => date('Y-m-d H:i:s', strtotime('-1 days')),
                'preguntas_respuestas' => json_encode([
                    'alimentacion' => 'Come poco, parece ansioso.',
                    'sueno' => 'Llora mucho durante la noche.',
                    'conducta' => 'Rompió algunos muebles cuando se quedó solo.'
                ]),
                'puntuacion' => 2,
                'comentarios' => 'Necesitamos ayuda con su ansiedad.',
                'necesita_seguimiento' => true
            ]
        ];

        $this->table('encuesta_adopcion')->insert($data)->saveData();
    }
}