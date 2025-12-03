<?php

namespace Tempest\Testing;

use Attribute;
use Closure;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Provide
{
    public array $entries;

    public function __construct(
        /** $var string|array[]|Closure $entries */
        string|array|Closure ...$entries,
    ) {
        $this->entries = $entries;
    }
}
