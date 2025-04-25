<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\MigrationDiscovery;
use Tempest\Database\Migrations\RunnableMigrations;
use Tempest\Discovery\DiscoveryLocation;
use Tests\Tempest\Fixtures\Discovery\HiddenDatabaseMigration;
use Tests\Tempest\Fixtures\Discovery\HiddenMigratableDatabaseMigration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\get;

/**
 * @internal
 */
#[CoversNothing]
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
                __DIR__ . '../../Fixtures/Discovery',
            ),
        ];

        $migrations = get(RunnableMigrations::class);

        $this->assertNotContains(HiddenDatabaseMigration::class, $migrations);
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
                __DIR__ . '../../Fixtures/Discovery',
            ),
        ];

        $migrations = get(RunnableMigrations::class);

        $foundMigrations = array_filter(
            iterator_to_array($migrations),
            static fn (DatabaseMigration $migration) => $migration instanceof HiddenMigratableDatabaseMigration,
        );
        $this->assertCount(1, $foundMigrations, 'Expected one hidden migration to be found');
    }
}
