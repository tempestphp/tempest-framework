<?php

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\HasTag;
use UnitEnum;

final class HasTagObject implements HasTag
{
    public function __construct(
        public string $name,
        public string|UnitEnum|null $tag = null,
    ) {}
}
