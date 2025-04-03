<?php

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\AllowDynamicTags;
use Tempest\Container\CurrentTag;

// Used in ContainerTest
#[AllowDynamicTags]
final class SubdependencyWithDynamicTag
{
    #[CurrentTag]
    public ?string $tag;
}
