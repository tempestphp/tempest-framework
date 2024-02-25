<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Commands;

use Tempest\AppConfig;
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
