<?php

declare(strict_types=1);

use ManualMuni\Support\DatabaseConnectionFactory;
use ManualMuni\Support\DotenvLoader;

require dirname(__DIR__) . '/vendor/autoload.php';

DotenvLoader::load(dirname(__DIR__));
$connection = (new DatabaseConnectionFactory())->create();

$connection->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        version VARCHAR(255) NOT NULL PRIMARY KEY,
        applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
);

$appliedVersions = $connection->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$appliedVersions = array_fill_keys($appliedVersions, true);
$migrationFiles = glob(__DIR__ . '/Migration/*.sql') ?: [];
sort($migrationFiles, SORT_NATURAL);

foreach ($migrationFiles as $migrationFile) {
    $version = basename($migrationFile);

    if (isset($appliedVersions[$version])) {
        continue;
    }

    $sql = file_get_contents($migrationFile);
    if ($sql === false) {
        throw new RuntimeException(sprintf('No se pudo leer la migración %s.', $version));
    }

    $connection->exec($sql);
    $connection->prepare('INSERT INTO schema_migrations (version) VALUES (?)')->execute([$version]);

    printf("Aplicada %s%s", $version, PHP_EOL);
}
