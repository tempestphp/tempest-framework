<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\AppConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class DiscoveryStatusCommandTest extends FrameworkIntegrationTestCase
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
