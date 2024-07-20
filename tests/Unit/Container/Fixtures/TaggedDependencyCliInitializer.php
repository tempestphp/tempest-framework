<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton('cli')]
final readonly class TaggedDependencyCliInitializer implements Initializer
{
    public function initialize(Container $container): TaggedDependency
    {
        return new TaggedDependency('cli');
    }
}
