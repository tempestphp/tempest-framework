<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Testing\IntegrationTest;

/**
 * @internal
 * @small
 */
class MigrationManagerTest extends IntegrationTest
{
    public function test_migration()
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->up();
        $migrations = Migration::all();
        $this->assertCount(3, $migrations);

        $migrationManager->up();
        $migrations = Migration::all();
        $this->assertCount(3, $migrations);
    }
}
