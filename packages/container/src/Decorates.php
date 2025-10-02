<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Decorates
{
    public function __construct(
        public string $decorates,
    ) {}
}
