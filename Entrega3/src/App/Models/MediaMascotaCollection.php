<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class MediaMascotaCollection extends Model
{
    public string $table = 'media_mascota';

    public function getByMascotaId(int $mascotaId): array
    {
        $rows = $this->queryBuilder->select($this->table, ['mascota_id' => $mascotaId]);

        $items = [];
        foreach ($rows as $row) {
            $media = new MediaMascota();
            $media->set($row);
            $items[] = $media;
        }
        
        return $items;
    }
}
