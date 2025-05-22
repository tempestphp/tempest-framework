<?php

namespace Tests\Tempest\Integration\Core\Config;

use Tempest\Core\ConfigCache;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Discovery\DiscoveryLocation;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class LoadConfigTest extends FrameworkIntegrationTestCase
{
    public function test_config_loaded_in_order()
    {
        $this->container->get(ConfigCache::class)->clear();

        $this->kernel->discoveryLocations = [
            new DiscoveryLocation('App', __DIR__ . '/Fixtures'),
        ];

        $config = $this->container->get(LoadConfig::class)->find();

        $this->assertStringContainsString('db.config.php', $config[0]);
        $this->assertStringContainsString('db.stg.config.php', $config[1]);
        $this->assertStringContainsString('db.local.config.php', $config[2]);
    }
}
