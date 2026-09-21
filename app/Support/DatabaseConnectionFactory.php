<?php

declare(strict_types=1);

namespace ManualMuni\Support;

use PDO;

final class DatabaseConnectionFactory
{
    public function create(): PDO
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $caPath = $_ENV['DB_SSL_CA'] ?? '';

        if ($caPath !== '') {
            if (!is_file($caPath)) {
                throw new \RuntimeException('No se encontró el certificado CA de MySQL configurado en DB_SSL_CA.');
            }

            $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $this->required('DB_HOST'),
            $this->required('DB_PORT'),
            $this->required('DB_DATABASE'),
        );

        return new PDO($dsn, $this->required('DB_USERNAME'), $this->required('DB_PASSWORD'), $options);
    }

    private function required(string $key): string
    {
        $value = $_ENV[$key] ?? '';

        if ($value === '') {
            throw new \RuntimeException(sprintf('Falta la variable de entorno %s.', $key));
        }

        return $value;
    }
}
