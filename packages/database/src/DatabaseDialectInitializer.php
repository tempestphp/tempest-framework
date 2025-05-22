<?php

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class DatabaseDialectInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->is(DatabaseDialect::class);
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        return $container->get(DatabaseConfig::class, $tag)->dialect;
    }
}
