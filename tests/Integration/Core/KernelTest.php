<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Core\FrameworkKernel;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;
use Tests\Tempest\Fixtures\TestDependency;

/**
 * @internal
 */
final class KernelTest extends TestCase
{
    #[Test]
    public function discovery_boot(): void
    {
        // TODO: Move this
        $kernel = FrameworkKernel::boot(
            root: getcwd(),
            discoveryLocations: [
                new DiscoveryLocation('Tests\\Tempest\\Fixtures\\', getcwd() . '/tests/Fixtures/'),
            ],
        );

        $discoveryConfig = $kernel->container->get(DiscoveryConfig::class);
        $this->assertNotEmpty($discoveryConfig->classes);

        $test = $kernel->container->get(TestDependency::class);

        $this->assertInstanceOf(TestDependency::class, $test);
        $this->assertSame('test', $test->input);
    }

    #[Test]
    public function kernel_start(): void
    {
        FrameworkKernel::boot(
            root: getcwd(),
        );

        $this->assertTrue(defined('TEMPEST_START')); // @phpstan-ignore method.alreadyNarrowedType
    }
}
