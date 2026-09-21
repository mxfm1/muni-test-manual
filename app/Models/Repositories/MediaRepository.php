<?php

declare(strict_types=1);

namespace ManualMuni\Models\Repositories;

use ManualMuni\Models\Media;
use PDO;

final readonly class MediaRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function save(Media $media): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO media (section_id, storage_key, url, filename, mime_type, size_bytes, width, height, alt_text)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        );
        $statement->execute([
            $media->sectionId,
            $media->storageKey,
            $media->url,
            $media->filename,
            $media->mimeType,
            $media->sizeInBytes,
            $media->width,
            $media->height,
            $media->altText,
        ]);
    }

    /** @return list<Media> */
    public function findByModuleId(int $moduleId): array
    {
        $statement = $this->connection->prepare(
            'SELECT media.id, media.section_id, media.storage_key, media.url, media.filename, media.mime_type,
                    media.size_bytes, media.width, media.height, media.alt_text, media.created_at
             FROM media
             INNER JOIN sections ON sections.id = media.section_id
             INNER JOIN topics ON topics.id = sections.topic_id
             WHERE topics.module_id = ?',
        );
        $statement->execute([$moduleId]);

        return array_map(
            static fn (array $row): Media => new Media(
                (int) $row['id'],
                (int) $row['section_id'],
                $row['storage_key'],
                $row['url'],
                $row['filename'],
                $row['mime_type'],
                $row['size_bytes'] === null ? null : (int) $row['size_bytes'],
                $row['width'] === null ? null : (int) $row['width'],
                $row['height'] === null ? null : (int) $row['height'],
                $row['alt_text'],
                new \DateTimeImmutable($row['created_at']),
            ),
            $statement->fetchAll(),
        );
    }

    /** @return list<Media> */
    public function findBySectionId(int $sectionId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, section_id, storage_key, url, filename, mime_type, size_bytes, width, height, alt_text, created_at
             FROM media
             WHERE section_id = ?',
        );
        $statement->execute([$sectionId]);

        return array_map(
            static fn (array $row): Media => new Media(
                (int) $row['id'],
                (int) $row['section_id'],
                $row['storage_key'],
                $row['url'],
                $row['filename'],
                $row['mime_type'],
                $row['size_bytes'] === null ? null : (int) $row['size_bytes'],
                $row['width'] === null ? null : (int) $row['width'],
                $row['height'] === null ? null : (int) $row['height'],
                $row['alt_text'],
                new \DateTimeImmutable($row['created_at']),
            ),
            $statement->fetchAll(),
        );
    }

    /** @return list<Media> */
    public function findByTopicId(int $topicId): array
    {
        $statement = $this->connection->prepare(
            'SELECT media.id, media.section_id, media.storage_key, media.url, media.filename, media.mime_type,
                    media.size_bytes, media.width, media.height, media.alt_text, media.created_at
             FROM media
             INNER JOIN sections ON sections.id = media.section_id
             WHERE sections.topic_id = ?',
        );
        $statement->execute([$topicId]);

        return array_map(
            static fn (array $row): Media => new Media(
                (int) $row['id'],
                (int) $row['section_id'],
                $row['storage_key'],
                $row['url'],
                $row['filename'],
                $row['mime_type'],
                $row['size_bytes'] === null ? null : (int) $row['size_bytes'],
                $row['width'] === null ? null : (int) $row['width'],
                $row['height'] === null ? null : (int) $row['height'],
                $row['alt_text'],
                new \DateTimeImmutable($row['created_at']),
            ),
            $statement->fetchAll(),
        );
    }
}
