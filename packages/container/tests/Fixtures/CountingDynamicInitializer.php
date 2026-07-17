<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class CountingDynamicInitializer implements DynamicInitializer
{
    public static int $instances = 0;

    private bool $counted = false;

    public static function reset(): void
    {
        self::$instances = 0;
    }

    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        if (! $this->counted) {
            $this->counted = true;
            self::$instances++;
        }

        return false;
    }

    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): object
    {
        throw new \LogicException('CountingDynamicInitializer should not initialize objects.');
    }
}
