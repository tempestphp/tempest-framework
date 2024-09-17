<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tests\Tempest\Integration\Console\Commands\MyDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class DiscoveryCacheCommandTest extends FrameworkIntegrationTestCase
{
    public function test_it_clears_discovery_cache(): void
    {
        MyDiscovery::$cached = false;

        $this->kernel->discoveryClasses = [MyDiscovery::class];

        $this->console->call('discovery:cache');

        $this->assertTrue(MyDiscovery::$cached);
    }
}
