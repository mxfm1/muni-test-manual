<?php

declare(strict_types=1);

namespace ManualMuni\Services;

use Intervention\Image\ImageManager;
use ManualMuni\Models\Media;

final readonly class LocalImageStorage implements ImageStorage
{
    private const MAX_SIZE_IN_BYTES = 5_242_880;
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private string $directory,
        private string $publicBasePath,
        private ImageManager $imageManager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver()),
    ) {
    }

    public function store(int $sectionId, string $temporaryPath, string $filename, string $mimeType, int $sizeInBytes): Media
    {
        if ($sizeInBytes === 0 || $sizeInBytes > self::MAX_SIZE_IN_BYTES) {
            throw new \InvalidArgumentException('La imagen debe pesar entre 1 byte y 5 MB.');
        }

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException('Sólo se permiten imágenes JPEG, PNG o WebP.');
        }

        $this->ensureDirectoryExists();

        $id = bin2hex(random_bytes(16));
        $storageKey = $id . '.webp';
        $normalizedImage = $this->imageManager->read($temporaryPath)->scaleDown(width: 1920, height: 1920);
        $bytes = $normalizedImage->toWebp(quality: 82)->toString();

        if (file_put_contents($this->directory . '/' . $storageKey, $bytes, LOCK_EX) === false) {
            throw new \RuntimeException('No fue posible guardar la imagen localmente.');
        }

        return new Media(
            null,
            $sectionId,
            'media/' . $storageKey,
            rtrim($this->publicBasePath, '/') . '/' . $storageKey,
            $filename,
            'image/webp',
            $sizeInBytes,
            $normalizedImage->width(),
            $normalizedImage->height(),
            null,
        );
    }

    public function delete(Media $media): void
    {
        $path = $this->directory . '/' . basename($media->storageKey);

        if (is_file($path) && !unlink($path)) {
            throw new \RuntimeException('No fue posible eliminar la imagen local.');
        }
    }

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            throw new \RuntimeException('No fue posible crear el directorio de imágenes.');
        }
    }
}
