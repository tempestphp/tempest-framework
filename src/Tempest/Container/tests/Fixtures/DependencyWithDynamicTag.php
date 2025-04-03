<?php

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\AllowDynamicTags;
use Tempest\Container\CurrentTag;
use Tempest\Container\ForwardTag;

// Used in ContainerTest
#[AllowDynamicTags]
final class DependencyWithDynamicTag
{
    #[CurrentTag]
    public ?string $tag;

    public function __construct(
        #[ForwardTag]
        public readonly SubdependencyWithDynamicTag $subependency,
    ) {}
}
