<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class MediaMascotaCollection extends Model
{
    public string $table = 'media_mascota';

    public function getMultimedia(int $mascotaId, ?string $imagenPrincipal): array
    {
        $rows = $this->queryBuilder->select('media_mascota', ['mascota_id' => $mascotaId]);

        $media = [];
        foreach ($rows as $row) {
            $item = (object) $row;
            if ($row['tipo'] === 'video') {
                $item->poster = $imagenPrincipal ? "/assets/img/{$imagenPrincipal}" : null;
            }
            $media[] = $item;
        }

        return $media;
    }
}
