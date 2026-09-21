<?php

declare(strict_types=1);

namespace ManualMuni\Support;

final class CsrfTokenManager
{
    private const SESSION_KEY = 'guide_image_csrf_token';

    public function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public function isValid(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION[self::SESSION_KEY])
            && hash_equals($_SESSION[self::SESSION_KEY], $token);
    }
}
