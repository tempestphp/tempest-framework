<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Tag;

#[Tag('web')]
final readonly class TaggedDependencyWebInitializer implements Initializer
{
    public function initialize(Container $container): TaggedDependency
    {
        return new TaggedDependency('web');
    }
}
