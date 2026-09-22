<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Services\ImageStorage;

final readonly class MediaProxyController
{
    private const CACHE_SECONDS = 31_536_000;

    public function __construct(private ImageStorage $storage)
    {
    }

    public function show(string $storageKey): never
    {
        $stored = $this->storage->read($storageKey);

        if ($stored === null) {
            http_response_code(404);
            exit;
        }

        http_response_code(200);
        header('Content-Type: ' . $stored->mimeType);
        header('Content-Length: ' . strlen($stored->bytes));
        header('Cache-Control: public, max-age=' . self::CACHE_SECONDS . ', immutable');
        echo $stored->bytes;
        exit;
    }
}