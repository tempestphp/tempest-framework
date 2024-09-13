<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class MigrationManagerTest extends FrameworkIntegrationTestCase
{
    public function test_migration(): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->up();

        $migrations = Migration::all();
        $this->assertCount(5, $migrations);

        $migrationManager->up();
        $migrations = Migration::all();
        $this->assertCount(5, $migrations);

        $this->assertSame('2024-08-16-create_publishers_table_0', $migrations[3]->name);
        $this->assertSame('2024-08-16-create_publishers_table_1', $migrations[4]->name);
    }
}
