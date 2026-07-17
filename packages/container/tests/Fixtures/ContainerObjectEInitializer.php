<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class ContainerObjectEInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getName() === ContainerObjectE::class;
    }

    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): object
    {
        return new ContainerObjectE();
    }
}
