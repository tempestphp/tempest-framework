<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
class SingletonInitializer implements Initializer
{
    public function initialize(Container $container): ContainerObjectE
    {
        return new ContainerObjectE();
    }
}
