<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\AppConfig;
use Tests\Tempest\Integration\Console\Fixtures\MyDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
class DiscoveryClearCommandTest extends FrameworkIntegrationTest
{
    public function test_it_clears_discovery_cache()
    {
        $appConfig = $this->container->get(AppConfig::class);

        MyDiscovery::$cacheCleared = false;

        $appConfig->discoveryClasses = [MyDiscovery::class];

        $this->console->call('discovery:clear');

        $this->assertTrue(MyDiscovery::$cacheCleared);
    }
}
