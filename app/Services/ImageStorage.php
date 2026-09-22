<?php

declare(strict_types=1);

namespace ManualMuni\Services;

use ManualMuni\Models\Media;
use ManualMuni\Support\StoredImage;

interface ImageStorage
{
    public function store(int $sectionId, string $temporaryPath, string $filename, string $mimeType, int $sizeInBytes): Media;

    public function delete(Media $media): void;

    public function read(string $storageKey): ?StoredImage;

    public function publicUrl(string $storageKey): string;
}
