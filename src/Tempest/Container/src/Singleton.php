<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;
use UnitEnum;

#[Attribute]
final readonly class Singleton
{
    public function __construct(
        public null|string|UnitEnum $tag = null,
    ) {}
}
