<?php

namespace Tests\Tempest\Fixtures\Container;

use Tempest\Container\AllowDynamicTags;

#[AllowDynamicTags]
final readonly class DependencyWithDynamicTag
{
    public function __construct(
        public ?string $tag,
    ) {}
}
