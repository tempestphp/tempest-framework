<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;

class ContainerObjectEInitializer implements DynamicInitializer
{
    public function canInitialize(string $className): bool
    {
        return $className === ContainerObjectE::class;
    }

    public function initialize(string $className, Container $container): ContainerObjectE
    {
        return new ContainerObjectE();
    }
}
