<?php

namespace Tests\Tempest\Fixtures\Container;

use Tempest\Container\AllowDynamicTags;
use Tempest\Container\Container;
use Tempest\Container\CurrentTag;
use Tempest\Container\Initializer;

#[AllowDynamicTags]
final class TestDependencyInitializer implements Initializer
{
    #[CurrentTag]
    private ?string $tag; // @phpstan-ignore-line this is injected

    public function initialize(Container $container): DependencyWithDynamicTag
    {
        return new DependencyWithDynamicTag($this->tag);
    }
}
