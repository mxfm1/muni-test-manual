<?php

declare(strict_types=1);

namespace ManualMuni\Models;

final readonly class Media
{
    public function __construct(
        public ?int $id,
        public int $sectionId,
        public string $storageKey,
        public string $url,
        public string $filename,
        public string $mimeType,
        public ?int $sizeInBytes,
        public ?int $width,
        public ?int $height,
        public ?string $altText,
        public ?\DateTimeImmutable $createdAt = null,
    ) {
    }
}
