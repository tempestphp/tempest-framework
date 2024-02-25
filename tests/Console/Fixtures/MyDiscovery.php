<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;

class MyDiscovery implements Discovery
{
    public static bool $cacheCleared = false;

    public function discover(ReflectionClass $class): void
    {
        // TODO: Implement discover() method.
    }

    public function hasCache(): bool
    {
        return false;
    }

    public function storeCache(): void
    {
        // TODO: Implement storeCache() method.
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
