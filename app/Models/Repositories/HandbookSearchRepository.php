<?php

declare(strict_types=1);

namespace ManualMuni\Models\Repositories;

use ManualMuni\Models\SearchResult;
use PDO;

final readonly class HandbookSearchRepository
{
    public function __construct(private PDO $connection)
    {
    }

    /** @return list<SearchResult> */
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $booleanQuery = self::buildBooleanQuery($query);
        if ($booleanQuery === '') {
            return [];
        }

        $statement = $this->connection->prepare(
            "SELECT 'module' AS type, m.id, NULL AS parent_id, m.title, m.description AS excerpt,
                    NULL AS module_id, NULL AS module_title, NULL AS topic_title,
                    MATCH(m.title, m.description) AGAINST (? IN BOOLEAN MODE) AS relevance
             FROM modules m
             WHERE MATCH(m.title, m.description) AGAINST (? IN BOOLEAN MODE)
             UNION ALL
             SELECT 'topic' AS type, t.id, t.module_id AS parent_id, t.title, t.description AS excerpt,
                    t.module_id AS module_id, m.title AS module_title, NULL AS topic_title,
                    MATCH(t.title, t.description) AGAINST (? IN BOOLEAN MODE) AS relevance
             FROM topics t
             JOIN modules m ON m.id = t.module_id
             WHERE MATCH(t.title, t.description) AGAINST (? IN BOOLEAN MODE)
             UNION ALL
             SELECT 'section' AS type, s.id, s.topic_id AS parent_id, COALESCE(s.title, 'Sin título') AS title, s.content AS excerpt,
                    t.module_id AS module_id, m.title AS module_title, t.title AS topic_title,
                    MATCH(s.title, s.content) AGAINST (? IN BOOLEAN MODE) AS relevance
             FROM sections s
             JOIN topics t ON t.id = s.topic_id
             JOIN modules m ON m.id = t.module_id
             WHERE MATCH(s.title, s.content) AGAINST (? IN BOOLEAN MODE)
             ORDER BY relevance DESC, type ASC, id ASC
             LIMIT 25",
        );
        $statement->execute(array_fill(0, 6, $booleanQuery));

        return array_map(
            static fn (array $row): SearchResult => new SearchResult(
                $row['type'],
                (int) $row['id'],
                $row['parent_id'] === null ? null : (int) $row['parent_id'],
                $row['title'],
                $row['excerpt'],
                (float) $row['relevance'],
                $row['module_id'] === null ? null : (int) $row['module_id'],
                $row['module_title'],
                $row['topic_title'],
            ),
            $statement->fetchAll(),
        );
    }

    /**
     * Convierte una consulta libre en una expresión compatible con FULLTEXT
     * BOOLEAN MODE: cada término se normaliza a caracteres de palabra y se le
     * agrega el comodín de prefijo al final para matchear raíces mientras se
     * escribe. Los operadores reservados se eliminan para evitar inyección de
     * sintaxis en la búsqueda.
     */
    private static function buildBooleanQuery(string $query): string
    {
        $terms = [];

        foreach (preg_split('/\s+/', $query) ?: [] as $term) {
            $term = mb_strtolower($term);
            $term = preg_replace('/[^\p{L}\p{N}]+/u', '', $term);

            if ($term === '') {
                continue;
            }

            $terms[] = $term . '*';
        }

        return implode(' ', $terms);
    }
}
