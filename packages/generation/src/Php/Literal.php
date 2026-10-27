<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

/**
 * A PHP expression that is printed as-is instead of being exported as a value.
 */
final readonly class Literal
{
    public function __construct(
        public string $code,
    ) {}
}
