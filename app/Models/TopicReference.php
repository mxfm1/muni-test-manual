<?php

declare(strict_types=1);

namespace ManualMuni\Models;

final readonly class TopicReference
{
    public function __construct(
        public ?int $id,
        public int $topicId,
        public int $referencedTopicId,
        public ?string $label,
        public ?\DateTimeImmutable $createdAt = null,
    ) {
    }
}
