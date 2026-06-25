<?php

namespace Paw\App\Helpers;

use Google\Cloud\Storage\StorageClient;

class GCSHelper
{
    private static string $bucketName = 'pawmap-fotos';

    public static function subir(array $archivo, string $prefijo): string
    {
        $storage = new StorageClient();
        $bucket = $storage->bucket(self::$bucketName);
        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        $nombreObjeto = $prefijo . '/' . uniqid('', true) . '.' . $extension;

        $bucket->upload(fopen($archivo['tmp_name'], 'r'), ['name' => $nombreObjeto]);

        return "https://storage.googleapis.com/" . self::$bucketName . "/$nombreObjeto";
    }

    public static function borrar(string $url): void
    {
        if (!self::esUrlBucket($url)) {
            return;
        }
        
        $storage = new StorageClient();
        $bucket = $storage->bucket(self::$bucketName);
        $objectName = str_replace(
            "https://storage.googleapis.com/" . self::$bucketName . "/",
            '',
            $url
        );
        try {
            $bucket->object($objectName)->delete();
        } catch (\Exception $e) {}
    }

    public static function esUrlBucket(string $url): bool
    {
        return str_starts_with($url, 'https://storage.googleapis.com/');
    }
}
