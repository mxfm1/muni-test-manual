<?php

declare(strict_types=1);

namespace ManualMuni\Support;

use Dotenv\Dotenv;

final class DotenvLoader
{
    public static function load(string $projectRoot): void
    {
        if (is_file($projectRoot . '/.env')) {
            Dotenv::createImmutable($projectRoot)->safeLoad();
        }
    }
}
