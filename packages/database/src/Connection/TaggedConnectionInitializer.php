<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class TaggedConnectionInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $tag !== null && $class->getName() === Connection::class;
    }

    #[Singleton]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): Connection
    {
        return $container->get(ConnectionInitializer::class)->initializeTagged($container, $tag);
    }
}
