<?php

declare(strict_types=1);

namespace ManualMuni\Models\Repositories;

use ManualMuni\Models\Section;
use PDO;

final readonly class SectionRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function exists(int $sectionId): bool
    {
        $statement = $this->connection->prepare('SELECT 1 FROM sections WHERE id = ?');
        $statement->execute([$sectionId]);

        return $statement->fetchColumn() !== false;
    }

    /** @param list<int> $topicIds @return list<Section> */
    public function findByTopicIds(array $topicIds): array
    {
        if ($topicIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($topicIds), '?'));
        $statement = $this->connection->prepare(
            "SELECT id, topic_id, title, content, position, created_at, updated_at
             FROM sections
             WHERE topic_id IN ($placeholders)
             ORDER BY topic_id ASC, position ASC, id ASC",
        );
        $statement->execute($topicIds);

        return array_map(
            static fn (array $row): Section => new Section(
                (int) $row['id'],
                (int) $row['topic_id'],
                $row['title'],
                $row['content'],
                (int) $row['position'],
                new \DateTimeImmutable($row['created_at']),
                new \DateTimeImmutable($row['updated_at']),
            ),
            $statement->fetchAll(),
        );
    }

    public function create(int $topicId, ?string $title, string $content): int
    {
        $statement = $this->connection->prepare('SELECT COALESCE(MAX(position), -1) + 1 FROM sections WHERE topic_id = ?');
        $statement->execute([$topicId]);
        $position = (int) $statement->fetchColumn();

        $statement = $this->connection->prepare('INSERT INTO sections (topic_id, title, content, position) VALUES (?, ?, ?, ?)');
        $statement->execute([$topicId, $title, $content, $position]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $sectionId, ?string $title, string $content): bool
    {
        $statement = $this->connection->prepare('UPDATE sections SET title = ?, content = ? WHERE id = ?');
        $statement->execute([$title, $content, $sectionId]);

        return $statement->rowCount() === 1 || $this->exists($sectionId);
    }

    public function delete(int $sectionId): bool
    {
        $statement = $this->connection->prepare('DELETE FROM sections WHERE id = ?');
        $statement->execute([$sectionId]);

        return $statement->rowCount() === 1;
    }

    public function findTopicId(int $sectionId): ?int
    {
        $statement = $this->connection->prepare('SELECT topic_id FROM sections WHERE id = ?');
        $statement->execute([$sectionId]);
        $topicId = $statement->fetchColumn();

        return $topicId === false ? null : (int) $topicId;
    }
}
