<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Commands;

use ReflectionClass;
use Tempest\AppConfig;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tests\Tempest\TestCase;

class DiscoveryClearCommandTest extends TestCase
{
    /** @test */
    public function it_clears_discovery_cache()
    {
        $appConfig = $this->container->get(AppConfig::class);

        MyDiscovery::$cacheCleared = false;

        $appConfig->discoveryClasses = [MyDiscovery::class];

        $this->console('discovery:clear');

        $this->assertTrue(MyDiscovery::$cacheCleared);
    }
}

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
