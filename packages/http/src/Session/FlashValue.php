<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

/**
 * Represents a value available for the next request only.
 */
final readonly class FlashValue
{
    public function __construct(
        public mixed $value,
    ) {}
}
