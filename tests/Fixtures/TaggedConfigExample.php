<?php

namespace Tests\Tempest\Fixtures;

use Tempest\Container\TaggedConfig;

final readonly class TaggedConfigExample implements TaggedConfig
{
    public function __construct(
        public string $tag,
        public string $property,
    ) {}
}
