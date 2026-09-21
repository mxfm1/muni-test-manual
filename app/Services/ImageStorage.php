<?php

declare(strict_types=1);

namespace ManualMuni\Services;

use ManualMuni\Models\Media;

interface ImageStorage
{
    public function store(int $sectionId, string $temporaryPath, string $filename, string $mimeType, int $sizeInBytes): Media;

    public function delete(Media $media): void;
}
