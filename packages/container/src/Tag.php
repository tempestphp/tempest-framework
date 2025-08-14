<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;
use UnitEnum;

#[Attribute]
final readonly class Tag
{
    public function __construct(
        public string|UnitEnum $name,
    ) {}
}
