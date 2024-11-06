<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\MigrationDiscovery;
use function Tempest\get;
use Tests\Tempest\Fixtures\Discovery\HiddenMigratableMigration;
use Tests\Tempest\Fixtures\Discovery\HiddenMigration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class LoadDiscoveryClassesTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function do_not_discover(): void
    {
        $this->kernel->discoveryClasses = [
            MigrationDiscovery::class,
        ];

        $this->kernel->discoveryLocations = [
            new DiscoveryLocation(
                'Tests\Tempest\Fixtures',
                __DIR__ . '../../Fixtures/Discovery'
            ),
        ];

        (new LoadDiscoveryClasses(
            $this->kernel,
            $this->container->get(DiscoveryCache::class),
            $this->container,
        ));

        $migrations = get(DatabaseConfig::class)->getMigrations();

        $this->assertNotContains(HiddenMigration::class, $migrations);
    }

    #[Test]
    public function do_not_discover_except(): void
    {
        $this->kernel->discoveryClasses = [
            MigrationDiscovery::class,
            // TODO: update tests to add `PublishDiscovery` when it's merged
        ];

        $this->kernel->discoveryLocations = [
            new DiscoveryLocation(
                'Tests\Tempest\Fixtures',
                __DIR__ . '../../Fixtures/Discovery'
            ),
        ];

        (new LoadDiscoveryClasses(
            kernel: $this->kernel,
            discoveryCache: $this->container->get(DiscoveryCache::class),
            container: $this->container,
        ));

        $migrations = get(DatabaseConfig::class)->getMigrations();

        $this->assertContains(HiddenMigratableMigration::class, $migrations);
    }
}
