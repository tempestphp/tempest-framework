<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;

#[Attribute]
final readonly class Tag
{
    public function __construct(
        public string $name,
    ) {}
}
