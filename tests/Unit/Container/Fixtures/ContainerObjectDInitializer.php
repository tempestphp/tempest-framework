<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

class ContainerObjectDInitializer implements Initializer
{
    public function initialize(Container $container): \Tests\Tempest\Unit\Container\Fixtures\ContainerObjectD
    {
        return new \Tests\Tempest\Unit\Container\Fixtures\ContainerObjectD(prop: 'test');
    }
}
