<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class MigrationManagerTest extends FrameworkIntegrationTestCase
{
    public function test_migration(): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->up();

        $migrations = Migration::all();
        $this->assertNotEmpty($migrations);
        $oldCount = count($migrations);

        $migrationManager->up();

        $migrations = Migration::all();
        $this->assertNotEmpty($migrations);
        $this->assertSame($oldCount, count($migrations));
    }
}
