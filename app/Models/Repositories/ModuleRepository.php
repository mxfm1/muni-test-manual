<?php

declare(strict_types=1);

namespace ManualMuni\Models\Repositories;

use ManualMuni\Models\HandbookModule;
use PDO;

final readonly class ModuleRepository
{
    public function __construct(private PDO $connection)
    {
    }

    /** @return list<HandbookModule> */
    public function findAll(): array
    {
        $rows = $this->connection->query(
            'SELECT id, title, description, position, created_at, updated_at FROM modules ORDER BY position ASC, id ASC',
        )->fetchAll();

        return array_map(
            static fn (array $row): HandbookModule => new HandbookModule(
                (int) $row['id'],
                $row['title'],
                $row['description'],
                (int) $row['position'],
                new \DateTimeImmutable($row['created_at']),
                new \DateTimeImmutable($row['updated_at']),
            ),
            $rows,
        );
    }

    public function findById(int $moduleId): ?HandbookModule
    {
        $statement = $this->connection->prepare(
            'SELECT id, title, description, position, created_at, updated_at FROM modules WHERE id = ?',
        );
        $statement->execute([$moduleId]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return new HandbookModule(
            (int) $row['id'],
            $row['title'],
            $row['description'],
            (int) $row['position'],
            new \DateTimeImmutable($row['created_at']),
            new \DateTimeImmutable($row['updated_at']),
        );
    }

    public function create(string $title, ?string $description = null): int
    {
        $position = (int) $this->connection->query('SELECT COALESCE(MAX(position), -1) + 1 FROM modules')->fetchColumn();
        $statement = $this->connection->prepare('INSERT INTO modules (title, description, position) VALUES (?, ?, ?)');
        $statement->execute([$title, $description, $position]);

        return (int) $this->connection->lastInsertId();
    }

    public function exists(int $moduleId): bool
    {
        $statement = $this->connection->prepare('SELECT 1 FROM modules WHERE id = ?');
        $statement->execute([$moduleId]);

        return $statement->fetchColumn() !== false;
    }

    public function update(int $moduleId, string $title, ?string $description = null): bool
    {
        $statement = $this->connection->prepare('UPDATE modules SET title = ?, description = ? WHERE id = ?');
        $statement->execute([$title, $description, $moduleId]);

        return $statement->rowCount() === 1 || $this->exists($moduleId);
    }

    public function delete(int $moduleId): bool
    {
        $statement = $this->connection->prepare('DELETE FROM modules WHERE id = ?');
        $statement->execute([$moduleId]);

        return $statement->rowCount() === 1;
    }
}
