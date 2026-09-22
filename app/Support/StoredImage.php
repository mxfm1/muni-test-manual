<?php

declare(strict_types=1);

namespace ManualMuni\Support;

final readonly class StoredImage
{
    public function __construct(
        public string $mimeType,
        public string $bytes,
    ) {
    }
}