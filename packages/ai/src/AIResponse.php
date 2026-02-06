<?php

declare(strict_types=1);

namespace Tempest\AI;

final readonly class AIResponse
{
    public function __construct(
        public string $content,
        public ?string $model = null,
        public ?int $promptTokens = null,
        public ?int $completionTokens = null,
        public ?int $totalTokens = null,
        public ?string $finishReason = null,
        public array $raw = [],
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }
}
