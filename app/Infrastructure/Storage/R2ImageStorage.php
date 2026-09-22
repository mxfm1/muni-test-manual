<?php

declare(strict_types=1);

namespace ManualMuni\Infrastructure\Storage;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Intervention\Image\ImageManager;
use ManualMuni\Models\Media;
use ManualMuni\Services\ImageStorage;
use ManualMuni\Support\NormalizedImageEncoder;
use ManualMuni\Support\StoredImage;

final readonly class R2ImageStorage implements ImageStorage
{
    private const MAX_SIZE_IN_BYTES = 5_242_880;
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private S3Client $client,
        private string $bucket,
        private string $publicBaseUrl,
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

        $normalizedImage = $this->imageManager->read($temporaryPath)->scaleDown(width: 1920, height: 1920);
        $encoded = NormalizedImageEncoder::encode($normalizedImage);
        $storageKey = 'media/' . bin2hex(random_bytes(16)) . '.' . $encoded->extension;

        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $storageKey,
            'Body' => $encoded->bytes,
            'ContentType' => $encoded->mimeType,
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        return new Media(
            null,
            $sectionId,
            $storageKey,
            $this->publicUrl($storageKey),
            $filename,
            $encoded->mimeType,
            strlen($encoded->bytes),
            $normalizedImage->width(),
            $normalizedImage->height(),
            null,
        );
    }

    public function delete(Media $media): void
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $media->storageKey,
        ]);
    }

    public function read(string $storageKey): ?StoredImage
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $storageKey,
            ]);
        } catch (S3Exception $exception) {
            if ($exception->getStatusCode() === 404) {
                return null;
            }

            throw $exception;
        }

        return new StoredImage(
            (string) ($result['ContentType'] ?? 'application/octet-stream'),
            (string) $result['Body']->getContents(),
        );
    }

    public function publicUrl(string $storageKey): string
    {
        return rtrim($this->publicBaseUrl, '/') . '/' . ltrim($storageKey, '/');
    }
}
