<?php

namespace Tests\Tempest\Integration\Core\Config;

use Tempest\Core\ConfigCache;
use Tempest\Core\Environment;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class LoadConfigTest extends FrameworkIntegrationTestCase
{
    protected function tearDown(): void
    {
        Filesystem\delete_directory(__DIR__ . '/Fixtures');

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Filesystem\ensure_directory_empty(__DIR__ . '/Fixtures');

        $this->container->get(ConfigCache::class)->clear();
        $this->kernel->discoveryLocations = [
            new DiscoveryLocation('App', __DIR__ . '/Fixtures'),
        ];
    }

    public function test_config_loaded_in_order(): void
    {
        $this->setupFixtures([
            'db.local.config.php',
            'db.config.php',
            'db.stg.config.php',
            'db.prd.config.php',
            'db.test.config.php',
            'db.testing.config.php',
            'db.production.config.php',
        ]);

        $config = $this->container->get(LoadConfig::class)->find();

        $this->assertStringContainsString('db.config.php', $config[0]);
        $this->assertStringContainsString('db.stg.config.php', $config[1]);
        $this->assertStringContainsString('db.prd.config.php', $config[2]);
        $this->assertStringContainsString('db.production.config.php', $config[3]);
        $this->assertStringContainsString('db.local.config.php', $config[4]);
        $this->assertStringContainsString('db.test.config.php', $config[5]);
        $this->assertStringContainsString('db.testing.config.php', $config[6]);
    }

    public function test_non_production_configs_are_discarded_in_production(): void
    {
        $this->setupFixtures([
            'db.local.config.php',
            'db.dev.config.php',
            'db.stg.config.php',
            'db.production.config.php',
            'db.config.php',
        ]);

        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $config = $this->container->get(LoadConfig::class)->find();

        $this->assertCount(2, $config);
        $this->assertStringContainsString('db.config.php', $config[0]);
        $this->assertStringContainsString('db.production.config.php', $config[1]);
    }

    public function test_non_staging_configs_are_discarded_in_staging(): void
    {
        $this->setupFixtures([
            'db.local.config.php',
            'db.dev.config.php',
            'db.stg.config.php',
            'db.production.config.php',
            'db.config.php',
        ]);

        $this->container->singleton(Environment::class, Environment::STAGING);

        $config = $this->container->get(LoadConfig::class)->find();

        $this->assertCount(2, $config);
        $this->assertStringContainsString('db.config.php', $config[0]);
        $this->assertStringContainsString('db.stg.config.php', $config[1]);
    }

    public function test_non_dev_configs_are_discarded_in_dev(): void
    {
        $this->setupFixtures([
            'db.local.config.php',
            'db.dev.config.php',
            'db.stg.config.php',
            'db.production.config.php',
            'db.config.php',
        ]);

        $this->container->singleton(Environment::class, Environment::LOCAL);

        $config = $this->container->get(LoadConfig::class)->find();

        $this->assertCount(3, $config);
        $this->assertStringContainsString('db.config.php', $config[0]);
        $this->assertStringContainsString('db.dev.config.php', $config[1]);
        $this->assertStringContainsString('db.local.config.php', $config[2]);
    }

    private function setupFixtures(array $configs): void
    {
        foreach ($configs as $config) {
            $db = str_replace('.php', '.sqlite', $config);

            Filesystem\write_file(__DIR__ . '/Fixtures/' . $config, <<<PHP
            <?php

            use Tempest\Database\Config\SQLiteConfig;

            return new SQLiteConfig(
                path: '{$db}',
            );
            PHP);
        }
    }
}
