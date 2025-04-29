<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class SingletonInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ContainerObjectE
    {
        return new ContainerObjectE();
    }
}
