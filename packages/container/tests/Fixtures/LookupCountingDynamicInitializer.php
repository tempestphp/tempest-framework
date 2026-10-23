<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use LogicException;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class LookupCountingDynamicInitializer implements DynamicInitializer
{
    /** @var array<string, int> */
    public static array $lookups = [];

    public static function reset(): void
    {
        self::$lookups = [];
    }

    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        self::$lookups[$class->getName()] = (self::$lookups[$class->getName()] ?? 0) + 1;

        return false;
    }

    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): object
    {
        throw new LogicException('LookupCountingDynamicInitializer should not initialize objects.');
    }
}
