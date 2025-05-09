<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\Migrations\RunnableMigrations;
use Tests\Tempest\Fixtures\Discovery\HiddenDatabaseMigration;
use Tests\Tempest\Fixtures\Discovery\HiddenMigratableDatabaseMigration;
use Tests\Tempest\Fixtures\GlobalHiddenDiscovery;
use Tests\Tempest\Fixtures\GlobalHiddenPathDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\get;

/**
 * @internal
 */
final class LoadDiscoveryClassesTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function do_not_discover(): void
    {
        $migrations = get(RunnableMigrations::class);

        $this->assertNotContains(HiddenDatabaseMigration::class, $migrations);
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
        $migrations = get(RunnableMigrations::class);

        $foundMigrations = array_filter(
            iterator_to_array($migrations),
            static fn (DatabaseMigration $migration) => $migration instanceof HiddenMigratableDatabaseMigration,
        );
        $this->assertCount(1, $foundMigrations, 'Expected one hidden migration to be found');
    }
}
