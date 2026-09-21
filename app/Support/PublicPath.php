<?php

declare(strict_types=1);

namespace ManualMuni\Support;

final class PublicPath
{
    public static function for(string $path): string
    {
        $projectPublicDirectory = realpath(dirname(__DIR__, 2) . '/public');
        $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        $prefix = $projectPublicDirectory !== false && $projectPublicDirectory === $documentRoot ? '' : '/public';

        return $prefix . '/' . ltrim($path, '/');
    }
}
