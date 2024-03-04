<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\AppConfig;
use Tempest\Testing\IntegrationTest;

/**
 * @internal
 * @small
 */
class DiscoveryStatusCommandTest extends IntegrationTest
{
    public function test_discovery_status_command()
    {
        $output = $this->console->call('discovery:status');

        $appConfig = $this->container->get(AppConfig::class);

        foreach ($appConfig->discoveryClasses as $discoveryClass) {
            $output->assertContains($discoveryClass);
        }

        foreach ($appConfig->discoveryLocations as $discoveryLocation) {
            $output->assertContains($discoveryLocation->path);
        }
    }
}
