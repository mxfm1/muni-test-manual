<?php

declare(strict_types=1);

namespace ManualMuni\Models\Repositories;

use ManualMuni\Models\Topic;
use PDO;

final readonly class TopicRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function create(int $moduleId, string $title): int
    {
        $statement = $this->connection->prepare('SELECT COALESCE(MAX(position), -1) + 1 FROM topics WHERE module_id = ?');
        $statement->execute([$moduleId]);
        $position = (int) $statement->fetchColumn();

        $statement = $this->connection->prepare('INSERT INTO topics (module_id, title, position) VALUES (?, ?, ?)');
        $statement->execute([$moduleId, $title, $position]);

        return (int) $this->connection->lastInsertId();
    }

    public function exists(int $topicId): bool
    {
        $statement = $this->connection->prepare('SELECT 1 FROM topics WHERE id = ?');
        $statement->execute([$topicId]);

        return $statement->fetchColumn() !== false;
    }

    public function findById(int $topicId): ?Topic
    {
        $statement = $this->connection->prepare(
            'SELECT id, module_id, title, description, position, created_at, updated_at FROM topics WHERE id = ?',
        );
        $statement->execute([$topicId]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return new Topic(
            (int) $row['id'],
            (int) $row['module_id'],
            $row['title'],
            $row['description'],
            (int) $row['position'],
            new \DateTimeImmutable($row['created_at']),
            new \DateTimeImmutable($row['updated_at']),
        );
    }

    public function updateTitle(int $topicId, string $title): bool
    {
        $statement = $this->connection->prepare('UPDATE topics SET title = ? WHERE id = ?');
        $statement->execute([$title, $topicId]);

        return $statement->rowCount() === 1 || $this->exists($topicId);
    }

    public function delete(int $topicId): bool
    {
        $statement = $this->connection->prepare('DELETE FROM topics WHERE id = ?');
        $statement->execute([$topicId]);

        return $statement->rowCount() === 1;
    }

    public function findModuleId(int $topicId): ?int
    {
        $statement = $this->connection->prepare('SELECT module_id FROM topics WHERE id = ?');
        $statement->execute([$topicId]);
        $moduleId = $statement->fetchColumn();

        return $moduleId === false ? null : (int) $moduleId;
    }

    /** @param list<int> $moduleIds @return list<Topic> */
    public function findByModuleIds(array $moduleIds): array
    {
        if ($moduleIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($moduleIds), '?'));
        $statement = $this->connection->prepare(
            "SELECT id, module_id, title, description, position, created_at, updated_at
             FROM topics
             WHERE module_id IN ($placeholders)
             ORDER BY module_id ASC, position ASC, id ASC",
        );
        $statement->execute($moduleIds);

        return array_map(
            static fn (array $row): Topic => new Topic(
                (int) $row['id'],
                (int) $row['module_id'],
                $row['title'],
                $row['description'],
                (int) $row['position'],
                new \DateTimeImmutable($row['created_at']),
                new \DateTimeImmutable($row['updated_at']),
            ),
            $statement->fetchAll(),
        );
    }
}
