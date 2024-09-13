<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;

class ContainerObjectEInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool
    {
        return $class->getName() === ContainerObjectE::class;
    }

    public function initialize(ClassReflector $class, Container $container): object
    {
        return new ContainerObjectE();
    }
}
