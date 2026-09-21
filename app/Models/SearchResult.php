<?php

declare(strict_types=1);

namespace ManualMuni\Models;

final readonly class SearchResult
{
    public function __construct(
        public string $type,
        public int $id,
        public ?int $parentId,
        public string $title,
        public ?string $excerpt,
        public float $relevance,
        public ?int $moduleId = null,
        public ?string $moduleTitle = null,
        public ?string $topicTitle = null,
    ) {
    }
}
