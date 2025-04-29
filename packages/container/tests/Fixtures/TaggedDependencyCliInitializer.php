<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class TaggedDependencyCliInitializer implements Initializer
{
    #[Singleton('cli')]
    public function initialize(Container $container): TaggedDependency
    {
        return new TaggedDependency('cli');
    }
}
