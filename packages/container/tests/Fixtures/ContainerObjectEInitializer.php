<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Tag;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class ContainerObjectEInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getName() === ContainerObjectE::class;
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        return new ContainerObjectE();
    }
}
