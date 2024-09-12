<?php

declare(strict_types=1);

namespace Tempest\Core\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Container;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\Kernel;
use Tests\Tempest\Fixtures\TestDependency;

/**
 * @internal
 * @small
 */
class KernelTest extends TestCase
{
    public function test_discovery_boot(): void
    {
        $kernel = new Kernel(
            root: __DIR__ . '/../../',
            discoveryLocations: [
                new DiscoveryLocation('Tests\\Tempest\\Fixtures\\', __DIR__ . '/../Fixtures/'),
            ],
        );

        $this->assertInstanceOf(Container::class, $kernel->container);

        $this->assertNotEmpty($kernel->discoveryClasses);

        $test = $kernel->container->get(TestDependency::class);

        $this->assertInstanceOf(TestDependency::class, $test);
        $this->assertSame('test', $test->input);
    }
}
