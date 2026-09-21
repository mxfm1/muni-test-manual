<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Models\Repositories\HandbookSearchRepository;
use ManualMuni\Support\TiptapDocument;

final readonly class SearchController
{
    public function __construct(
        private HandbookSearchRepository $searchRepository,
    ) {
    }

    public function search(): never
    {
        $query = $_GET['q'] ?? null;

        if (!is_string($query)) {
            $this->respond(400, ['ok' => false, 'error' => 'missing-query']);
        }

        $query = mb_substr(trim($query), 0, 60);

        if ($query === '') {
            $this->respond(200, ['ok' => true, 'results' => []]);
        }

        $results = $this->searchRepository->search($query);

        $this->respond(200, [
            'ok' => true,
            'results' => array_map(
                static function (\ManualMuni\Models\SearchResult $result): array {
                    $excerpt = $result->excerpt;

                    if ($result->type === 'section' && $excerpt !== null) {
                        $excerpt = TiptapDocument::toPlainText($excerpt);
                    }

                    return [
                        'type' => $result->type,
                        'id' => $result->id,
                        'parentId' => $result->parentId,
                        'moduleId' => $result->moduleId,
                        'moduleTitle' => $result->moduleTitle,
                        'topicTitle' => $result->topicTitle,
                        'title' => $result->title,
                        'excerpt' => $excerpt !== '' ? $excerpt : null,
                        'relevance' => $result->relevance,
                    ];
                },
                $results,
            ),
        ]);
    }

    private function respond(int $status, array $body): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
