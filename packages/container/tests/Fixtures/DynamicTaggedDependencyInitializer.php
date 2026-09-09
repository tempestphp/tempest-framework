<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class DynamicTaggedDependencyInitializer implements Initializer
{
    #[Singleton(dynamicTags: true)]
    public function initialize(Container $container): DynamicTaggedDependencyWithInitializer
    {
        return new DynamicTaggedDependencyWithInitializer();
    }
}
