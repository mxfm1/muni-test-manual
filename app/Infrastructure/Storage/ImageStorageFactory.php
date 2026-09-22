<?php

declare(strict_types=1);

namespace ManualMuni\Infrastructure\Storage;

use ManualMuni\Services\ImageStorage;
use ManualMuni\Services\LocalImageStorage;
use ManualMuni\Support\PublicPath;

final class ImageStorageFactory
{
    public static function create(string $projectRoot): ImageStorage
    {
        $driver = strtolower(trim((string) ($_ENV['MEDIA_STORAGE'] ?? 'local')));

        if ($driver === 'r2') {
            $publicBaseUrl = self::required('R2_PUBLIC_BASE_URL');

            return new R2ImageStorage(
                (new R2ClientFactory())->create(),
                self::required('R2_BUCKET'),
                $publicBaseUrl,
            );
        }

        if ($driver !== 'local') {
            throw new \RuntimeException(sprintf('Driver de media no soportado: %s.', $driver));
        }

        return new LocalImageStorage(
            $projectRoot . '/public/uploads/media',
            PublicPath::for('uploads/media'),
        );
    }

    private static function required(string $key): string
    {
        $value = trim((string) ($_ENV[$key] ?? ''));

        if ($value === '') {
            throw new \RuntimeException(sprintf('Falta la variable de entorno %s.', $key));
        }

        return $value;
    }
}
