<?php

namespace Tests\Tempest\Fixtures;

use Tempest\Container\TaggedConfig;
use UnitEnum;

final readonly class TaggedEnumConfigExample implements TaggedConfig
{
    public function __construct(
        public UnitEnum $tag,
        public string $property,
    ) {}
}
