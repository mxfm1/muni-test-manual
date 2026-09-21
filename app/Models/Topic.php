<?php

declare(strict_types=1);

namespace ManualMuni\Models;

final readonly class Topic
{
    public function __construct(
        public ?int $id,
        public int $moduleId,
        public string $title,
        public ?string $description,
        public int $position,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }
}
