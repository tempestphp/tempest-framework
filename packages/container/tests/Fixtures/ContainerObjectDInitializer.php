<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class ContainerObjectDInitializer implements Initializer
{
    public function initialize(Container $container): ContainerObjectD
    {
        return new ContainerObjectD(prop: 'test');
    }
}
