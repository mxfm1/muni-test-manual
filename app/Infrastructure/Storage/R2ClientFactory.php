<?php

declare(strict_types=1);

namespace ManualMuni\Infrastructure\Storage;

use Aws\S3\S3Client;

final class R2ClientFactory
{
    public function create(): S3Client
    {
        $accountId = $this->required('R2_ACCOUNT_ID');
        $endpoint = $_ENV['R2_ENDPOINT'] ?? 'https://' . $accountId . '.r2.cloudflarestorage.com';

        return new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => rtrim($endpoint, '/'),
            'credentials' => [
                'key' => $this->required('R2_ACCESS_KEY_ID'),
                'secret' => $this->required('R2_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    private function required(string $key): string
    {
        $value = trim((string) ($_ENV[$key] ?? ''));

        if ($value === '') {
            throw new \RuntimeException(sprintf('Falta la variable de entorno %s.', $key));
        }

        return $value;
    }
}
