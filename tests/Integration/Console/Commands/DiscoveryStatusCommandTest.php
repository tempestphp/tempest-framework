<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\AppConfig;
use Tests\Tempest\Integration\TestCase;

class DiscoveryStatusCommandTest extends TestCase
{
    /** @test */
    public function test_discovery_status_command()
    {
        $output = $this->console('discovery:status')->asText();

        $appConfig = $this->container->get(AppConfig::class);

        foreach ($appConfig->discoveryClasses as $discoveryClass) {
            $this->assertStringContainsString($discoveryClass, $output);
        }

        foreach ($appConfig->discoveryLocations as $discoveryLocation) {
            $this->assertStringContainsString($discoveryLocation->path, $output);
        }
    }
}
