<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;

final class ContainerObjectEInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag = null): bool
    {
        return $class->getName() === ContainerObjectE::class;
    }

    public function initialize(ClassReflector $class, Container $container, ?string $tag = null): object
    {
        return new ContainerObjectE();
    }
}
