<?php

declare(strict_types=1);

namespace Tempest\AI\Attribute;

use Attribute;

/**
 * Mark a method as an AI-powered handler.
 *
 * The method's return type and docblock will be used to instruct the AI
 * on how to structure the response.
 *
 * Usage:
 * ```php
 * #[AIHandler]
 * public function summarize(string $text): string
 * {
 *     return $this->ai->prompt("Summarize: {$text}")->content;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class AIHandler
{
    public function __construct(
        public ?string $model = null,
        public ?float $temperature = null,
        public ?int $maxTokens = null,
    ) {}
}
