<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit;

use PHPUnit\Framework\TestCase;
use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Http\RouteConfig;

/**
 * @internal
 * @small
 */
class KernelTest extends TestCase
{
    public function test_discovery()
    {
        $root = __DIR__ . '/../../';

        $kernel = new Kernel(
            $root,
            new AppConfig(
                root: $root,
                discoveryLocations: [
                    new DiscoveryLocation(
                        'App\\',
                        __DIR__ . '/../../app',
                    ),
                ],
            )
        );

        $container = $kernel->init();

        $config = $container->get(RouteConfig::class);

        $this->assertTrue(count($config->routes) > 1);
    }
}
