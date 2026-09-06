<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;

/**
 * Registers the class as a singleton that only lives for the duration of a single lifecycle, such as a request in a long-running worker.
 */
#[Attribute]
final readonly class Scoped
{
    public function __construct(
        public ?string $tag = null,
    ) {}
}
