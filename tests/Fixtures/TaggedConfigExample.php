<?php

namespace Tests\Tempest\Fixtures;

use Tempest\Container\TaggedConfig;

final class TaggedConfigExample implements TaggedConfig
{
    public function __construct(
        public readonly string $tag,
        public readonly string $property,
    ) {}
}
