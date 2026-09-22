<?php

declare(strict_types=1);

use ManualMuni\Infrastructure\Storage\ImageStorageFactory;
use ManualMuni\Support\DatabaseConnectionFactory;
use ManualMuni\Support\DotenvLoader;

require dirname(__DIR__) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__);
DotenvLoader::load($projectRoot);

$connection = (new DatabaseConnectionFactory())->create();
$storage = ImageStorageFactory::create($projectRoot);

if (strtolower((string) ($_ENV['MEDIA_STORAGE'] ?? '')) !== 'r2') {
    throw new RuntimeException('Definí MEDIA_STORAGE=r2 antes de ejecutar esta migración.');
}

$rows = $connection->query(
    'SELECT id, section_id, storage_key, filename, mime_type, size_bytes
     FROM media
     ORDER BY id ASC',
)->fetchAll();

$assetsBySection = [];
$migrated = 0;
$skipped = 0;

$updateMedia = $connection->prepare(
    'UPDATE media
     SET storage_key = ?, url = ?, mime_type = ?, size_bytes = ?, width = ?, height = ?
     WHERE id = ?',
);

foreach ($rows as $row) {
    $sourcePath = $projectRoot . '/public/uploads/' . ltrim((string) $row['storage_key'], '/');

    if (!is_file($sourcePath)) {
        printf("Omitido %d: no existe %s%s", $row['id'], $sourcePath, PHP_EOL);
        $skipped++;
        continue;
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($sourcePath);
    $size = filesize($sourcePath);
    if (!is_string($mimeType) || $size === false) {
        printf("Omitido %d: no se pudo leer metadata%s", $row['id'], PHP_EOL);
        $skipped++;
        continue;
    }

    try {
        $migratedMedia = $storage->store(
            (int) $row['section_id'],
            $sourcePath,
            (string) ($row['filename'] ?? 'image'),
            $mimeType,
            $size,
        );

        $updateMedia->execute([
            $migratedMedia->storageKey,
            $migratedMedia->url,
            $migratedMedia->mimeType,
            $migratedMedia->sizeInBytes,
            $migratedMedia->width,
            $migratedMedia->height,
            (int) $row['id'],
        ]);

        $assetsBySection[(int) $row['section_id']][] = [
            'id' => (int) $row['id'],
            'oldKey' => (string) $row['storage_key'],
            'newUrl' => $migratedMedia->url,
        ];
        $migrated++;
        printf("Migrado %d -> %s%s", $row['id'], $migratedMedia->storageKey, PHP_EOL);
    } catch (Throwable $exception) {
        printf("Error %d: %s%s", $row['id'], $exception->getMessage(), PHP_EOL);
        $skipped++;
    }
}

$sections = $connection->query('SELECT id, content FROM sections WHERE content IS NOT NULL')->fetchAll();
$updateSection = $connection->prepare('UPDATE sections SET content = ? WHERE id = ?');

foreach ($sections as $section) {
    $sectionId = (int) $section['id'];
    $assets = $assetsBySection[$sectionId] ?? [];
    if ($assets === []) {
        continue;
    }

    $document = json_decode((string) $section['content'], true);
    if (!is_array($document) || ($document['type'] ?? '') !== 'doc') {
        continue;
    }

    $changed = rewriteImageReferences($document, $assets);
    if ($changed) {
        $updateSection->execute([
            json_encode($document, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            $sectionId,
        ]);
        printf("Actualizado contenido de sección %d%s", $sectionId, PHP_EOL);
    }
}

printf("Migración finalizada. Migrados: %d. Omitidos: %d.%s", $migrated, $skipped, PHP_EOL);

/**
 * @param array<string, mixed> $node
 * @param list<array{id: int, oldKey: string, newUrl: string}> $assets
 */
function rewriteImageReferences(array &$node, array $assets): bool
{
    $changed = false;

    if (($node['type'] ?? '') === 'image' && is_array($node['attrs'] ?? null)) {
        $src = $node['attrs']['src'] ?? null;
        if (is_string($src)) {
            $path = parse_url($src, PHP_URL_PATH);
            $path = is_string($path) ? $path : $src;
            $filename = basename($path);

            foreach ($assets as $asset) {
                if (basename($asset['oldKey']) !== $filename) {
                    continue;
                }

                $node['attrs']['assetId'] = $asset['id'];
                $node['attrs']['src'] = $asset['newUrl'];
                $changed = true;
                break;
            }
        }
    }

    foreach ($node['content'] ?? [] as &$child) {
        if (is_array($child) && rewriteImageReferences($child, $assets)) {
            $changed = true;
        }
    }
    unset($child);

    return $changed;
}
