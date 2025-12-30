<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\RunnableMigrations;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Support\Arr;
use Tests\Tempest\Fixtures\Discovery\HiddenDatabaseMigration;
use Tests\Tempest\Fixtures\Discovery\HiddenMigratableDatabaseMigration;
use Tests\Tempest\Fixtures\GlobalHiddenDiscovery;
use Tests\Tempest\Fixtures\GlobalHiddenPathDiscovery;
use Tests\Tempest\Integration\Core\Fixtures\ManualTestDiscovery;
use Tests\Tempest\Integration\Core\Fixtures\ManualTestDiscoveryDependency;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class LoadDiscoveryClassesTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function do_not_discover(): void
    {
        $migrations = $this->container->get(RunnableMigrations::class);

        $this->assertFalse(Arr\contains($migrations, fn ($m) => $m instanceof HiddenDatabaseMigration));
    }

    #[Test]
    public function do_not_discover_global_class(): void
    {
        $this->assertFalse(GlobalHiddenDiscovery::$discovered);
    }

    #[Test]
    public function do_not_discover_global_path(): void
    {
        $this->assertFalse(GlobalHiddenPathDiscovery::$discovered);
    }

    #[Test]
    public function do_not_discover_except(): void
    {
        $migrations = $this->container->get(RunnableMigrations::class);

        $foundMigrations = Arr\filter(
            array: iterator_to_array($migrations),
            filter: static fn (MigratesUp $migration) => $migration instanceof HiddenMigratableDatabaseMigration,
        );

        $this->assertCount(1, $foundMigrations, 'Expected one hidden migration to be found');
    }

    #[Test]
    public function only_load_specific_discovery_classes(): void
    {
        /** @var ManualTestDiscoveryDependency $dependency */
        $dependency = $this->container->get(ManualTestDiscoveryDependency::class);
        $dependency->discovered = false;

        /** @var LoadDiscoveryClasses $loadDiscoveryClasses */
        $loadDiscoveryClasses = $this->container->get(LoadDiscoveryClasses::class);

        $loadDiscoveryClasses(discoveryClasses: [], discoveryLocations: []);

        $this->assertFalse($dependency->discovered);

        $loadDiscoveryClasses(
            discoveryClasses: [
                ManualTestDiscovery::class,
            ],
            discoveryLocations: [
                new DiscoveryLocation('Tests\Tempest\Integration\Core\Fixtures', __DIR__ . '/Fixtures'),
            ],
        );

        $this->assertTrue($dependency->discovered);
    }
}
