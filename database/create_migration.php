<?php

declare(strict_types=1);

$migrationName = trim(implode('_', array_slice($argv, 1)));
$migrationName = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $migrationName));
$migrationName = trim($migrationName, '_');

if ($migrationName === '') {
    fwrite(STDERR, "Uso: composer db:generate -- nombre_de_migracion\n");
    exit(1);
}

$migrationDirectory = __DIR__ . '/Migration';
$migrationFiles = glob($migrationDirectory . '/*.sql') ?: [];
$lastVersion = 0;

foreach ($migrationFiles as $migrationFile) {
    $filename = basename($migrationFile);

    if (preg_match('/^(\d+)_.*\.sql$/', $filename, $matches) === 1) {
        $lastVersion = max($lastVersion, (int) $matches[1]);
    }
}

$version = str_pad((string) ($lastVersion + 1), 3, '0', STR_PAD_LEFT);
$path = $migrationDirectory . '/' . $version . '_' . $migrationName . '.sql';
$handle = fopen($path, 'x');

if ($handle === false) {
    fwrite(STDERR, sprintf("No se pudo crear la migración %s.\n", $path));
    exit(1);
}

$contents = "-- Describe aquí los cambios de esta migración.\n";
fwrite($handle, $contents);
fclose($handle);

printf("Migración creada: %s%s", $path, PHP_EOL);
