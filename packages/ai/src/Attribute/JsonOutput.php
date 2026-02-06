<?php

declare(strict_types=1);

namespace Tempest\AI\Attribute;

use Attribute;

/**
 * Request structured JSON output from the AI.
 *
 * Usage:
 * ```php
 * #[JsonOutput]
 * public function getColors(): array
 * {
 *     $response = $this->ai->prompt('List 3 primary colors with hex codes');
 *     return json_decode($response->content, true);
 * }
 * ```
 *
 * With schema:
 * ```php
 * #[JsonOutput(schema: ['name' => 'string', 'hex' => 'string'])]
 * public function getColor(): array
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
final readonly class JsonOutput
{
    public function __construct(
        public ?array $schema = null,
    ) {}
}
