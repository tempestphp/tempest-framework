<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use function PHPUnit\Framework\assertNotContains;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\MigrationDiscovery;
use function Tempest\get;
use Tests\Tempest\Fixtures\Discovery\HiddenMigration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class LoadDiscoveryClassesTest extends FrameworkIntegrationTestCase
{
    public function test_hidden_from_discovery()
    {
        $this->kernel->discoveryClasses = [
            MigrationDiscovery::class,
        ];

        $this->kernel->discoveryLocations = [
            realpath(__DIR__.'../../Fixtures/Discovery'),
        ];

        (new LoadDiscoveryClasses($this->kernel, $this->container));

        $migrations = get(DatabaseConfig::class)->getMigrations();

        assertNotContains(HiddenMigration::class, $migrations);
    }
}
