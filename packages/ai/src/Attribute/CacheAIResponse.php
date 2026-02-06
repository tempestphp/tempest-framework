<?php

declare(strict_types=1);

namespace Tempest\AI\Attribute;

use Attribute;

/**
 * Cache AI responses to avoid redundant API calls.
 *
 * Usage:
 * ```php
 * #[CacheAIResponse(ttl: 3600)]
 * public function getFactAbout(string $topic): string
 * {
 *     return $this->ai->prompt("Give me a fact about {$topic}")->content;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CacheAIResponse
{
    public function __construct(
        /** Time to live in seconds */
        public int $ttl = 3600,
        /** Custom cache key prefix */
        public ?string $key = null,
    ) {}
}
