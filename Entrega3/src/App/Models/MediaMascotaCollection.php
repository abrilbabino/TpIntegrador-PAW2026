<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\App\Helpers\GCSHelper;
use Paw\Core\Traits\Notificable;

class MediaMascotaCollection extends Model
{
    use Notificable;
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

    public function agregarMedia(int $mascotaId, string $tipo, string $url): int
    {
        return $this->queryBuilder->insert($this->table, [
            'mascota_id' => $mascotaId,
            'tipo'       => $tipo,
            'url'        => $url
        ]);
    }

    public function procesarArchivoSeguimiento(int $mascotaId, string $tipoArchivo, ?int $registroId, string $url): void
    {
        if (in_array($tipoArchivo, ['comprobante', 'certificado']) && $registroId) {
            $this->queryBuilder->actualizarArchivoRegistroSanitario($registroId, $url, date('Y-m-d'));
        } else {
            $tipoMedia = 'certificado_med';
            if ($tipoArchivo === 'foto') {
                $tipoMedia = 'foto_seguimiento';
            } elseif ($tipoArchivo === 'certificado') {
                $tipoMedia = 'certificado_vac';
            }
            
            $this->queryBuilder->insert($this->table, [
                'mascota_id' => $mascotaId,
                'tipo' => $tipoMedia,
                'url' => $url
            ]);
        }
        
        // Logica de notificaciones (Adoptante -> Refugio)
        $mascotaData = $this->queryBuilder->selectOne('mascota', ['id' => $mascotaId]);
        if ($mascotaData) {
            $this->notificar(
                $mascotaData['refugio_id'],
                "Nueva Actualización de Seguimiento",
                "El adoptante subió nueva información fotográfica/documental de " . $mascotaData['nombre'],
                '/perfil'
            );
        }
    }

    public function eliminarFotoAdicional(int $mediaId, int $mascotaId): void
    {
        $db = $this->getQueryBuilder();
        $rows = $db->select($this->table, ['id' => $mediaId, 'mascota_id' => $mascotaId]);
        $foto = !empty($rows) ? current($rows) : null;

        if (empty($foto)) {
            throw new \Exception('Foto no encontrada.');
        }

        $url = trim((string) ($foto['url'] ?? ''));
        if ($url !== '') {
            if (GCSHelper::esUrlBucket($url)) {
                GCSHelper::borrar($url);
            } elseif (strpos($url, '..') === false) {
                $path = __DIR__ . '/../../../../public/' . ltrim($url, '/');
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }

        $db->delete($this->table, ['id' => $mediaId]);
    }
}
