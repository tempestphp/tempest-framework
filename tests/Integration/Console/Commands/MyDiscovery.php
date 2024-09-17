<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Reflection\ClassReflector;

final class MyDiscovery implements Discovery
{
    public static bool $cacheCleared = false;
    public static bool $cached = false;

    public function discover(ClassReflector $class): void
    {
        // TODO: Implement discover() method.
    }

    public function hasCache(): bool
    {
        return false;
    }

    public function storeCache(): void
    {
        self::$cached = true;
    }

    public function restoreCache(Container $container): void
    {
        // TODO: Implement restoreCache() method.
    }

    public function destroyCache(): void
    {
        self::$cacheCleared = true;
    }
}
