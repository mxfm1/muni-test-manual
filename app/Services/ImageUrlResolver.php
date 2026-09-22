<?php

declare(strict_types=1);

namespace ManualMuni\Services;

interface ImageUrlResolver
{
    public function resolve(int $assetId): ?string;
}
