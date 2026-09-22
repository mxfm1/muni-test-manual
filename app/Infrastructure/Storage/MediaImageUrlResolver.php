<?php

declare(strict_types=1);

namespace ManualMuni\Infrastructure\Storage;

use ManualMuni\Models\Repositories\MediaRepository;
use ManualMuni\Services\ImageStorage;
use ManualMuni\Services\ImageUrlResolver;

final readonly class MediaImageUrlResolver implements ImageUrlResolver
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private ImageStorage $storage,
    ) {
    }

    public function resolve(int $assetId): ?string
    {
        $media = $this->mediaRepository->findById($assetId);

        return $media === null ? null : $this->storage->publicUrl($media->storageKey);
    }
}
