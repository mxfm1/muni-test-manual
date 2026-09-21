<?php

declare(strict_types=1);

namespace ManualMuni\Models;

final readonly class Section
{
    public function __construct(
        public ?int $id,
        public int $topicId,
        public ?string $title,
        public ?string $content,
        public int $position,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }
}
