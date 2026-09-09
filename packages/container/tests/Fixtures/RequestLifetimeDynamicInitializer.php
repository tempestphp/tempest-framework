<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Lifetime;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class RequestLifetimeDynamicInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getName() === RequestLifetimeInterface::class;
    }

    #[Singleton(lifetime: Lifetime::REQUEST)]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): object
    {
        return new RequestLifetimeSingleton();
    }
}
