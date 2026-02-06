<?php

declare(strict_types=1);

namespace Tempest\AI\Attribute;

use Attribute;

/**
 * Define a system prompt for AI interactions on a class or method.
 *
 * Usage on class:
 * ```php
 * #[SystemPrompt('You are a helpful coding assistant.')]
 * final class CodeHelper
 * {
 *     public function __construct(private AIChat $ai) {}
 * }
 * ```
 *
 * Usage on method:
 * ```php
 * #[SystemPrompt('Respond only in JSON format.')]
 * public function getStructuredData(): AIResponse
 * {
 *     return $this->ai->prompt('List 3 colors');
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
final readonly class SystemPrompt
{
    public function __construct(
        public string $prompt,
    ) {}
}
