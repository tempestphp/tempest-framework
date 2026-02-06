<?php

declare(strict_types=1);

namespace Tempest\AI\Attribute;

use Attribute;
use Tempest\AI\AIProvider;

/**
 * Configure AI injection with specific settings.
 *
 * Usage:
 * ```php
 * public function __construct(
 *     #[WithAI(provider: AIProvider::ANTHROPIC, model: 'claude-3-opus')]
 *     private AIChat $ai,
 * ) {}
 * ```
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class WithAI
{
    public function __construct(
        public ?AIProvider $provider = null,
        public ?string $model = null,
        public ?float $temperature = null,
        public ?int $maxTokens = null,
        public ?string $systemPrompt = null,
    ) {}
}
