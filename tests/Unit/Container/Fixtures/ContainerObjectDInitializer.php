<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

class ContainerObjectDInitializer implements Initializer
{
    public function initialize(Container $container): ContainerObjectD
    {
        return new ContainerObjectD(prop: 'test');
    }
}
