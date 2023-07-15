<?php

declare(strict_types=1);

namespace Tests\Tempest\Database;

use Tempest\Database\MigrationManager;
use Tempest\ORM\Migration;
use Tests\Tempest\TestCase;

class MigrationManagerTest extends TestCase
{
    /** @test */
    public function test_migration()
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->up();
        $migrations = Migration::query()->get();
        $this->assertCount(2, $migrations);

        $migrationManager->up();
        $migrations = Migration::query()->get();
        $this->assertCount(2, $migrations);
    }
}
