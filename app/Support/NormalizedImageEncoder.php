<?php

declare(strict_types=1);

namespace ManualMuni\Support;

use Intervention\Image\Interfaces\ImageInterface;

final readonly class NormalizedImageEncoder
{
    public function __construct(
        public string $bytes,
        public string $extension,
        public string $mimeType,
    ) {
    }

    public static function encode(ImageInterface $image): self
    {
        if (function_exists('imagewebp')) {
            $encoded = $image->toWebp(quality: 82);

            return new self($encoded->toString(), 'webp', $encoded->mediaType());
        }

        $encoded = $image->toJpeg(quality: 85);

        return new self($encoded->toString(), 'jpg', $encoded->mediaType());
    }
}