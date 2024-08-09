<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

class SingletonInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ContainerObjectE
    {
        return new ContainerObjectE();
    }
}
