<?php

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\HasTag;
use UnitEnum;

final class HasTagObject implements HasTag
{
    public function __construct(
        public string $name,
        public null|string|UnitEnum $tag = null,
    ) {}
}
