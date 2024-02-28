<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

class ContainerObjectEInitializer implements Initializer, CanInitialize
{
    public function initialize(Container $container): \Tests\Tempest\Unit\Container\Fixtures\ContainerObjectE
    {
        return new \Tests\Tempest\Unit\Container\Fixtures\ContainerObjectE();
    }

    public function canInitialize(string $className): bool
    {
        return $className === \Tests\Tempest\Unit\Container\Fixtures\ContainerObjectE::class;
    }
}
