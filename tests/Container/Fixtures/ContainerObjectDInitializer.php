<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tests\Tempest\Container\Fixtures;

class ContainerObjectDInitializer implements Initializer
{
    public function initialize(Container $container): Fixtures\ContainerObjectD
    {
        return new Fixtures\ContainerObjectD(prop: 'test');
    }
}
