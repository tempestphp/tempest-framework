<?php

namespace Tempest\Upgrade\Tests\Tempest2;

use Tempest\Generation\Tests\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

final class Tempest2RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest2_rector.php');
    }

    public function test_migration_with_only_up(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MigrateUpMigration.input.php')
            ->assertMatchesExpected();
    }

    public function test_migration_with_up_and_down(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MigrateUpAndDownMigration.input.php')
            ->assertContains('implements \Tempest\Database\MigratesUp, \Tempest\Database\MigratesDown')
            ->assertContains('return new DropTableStatement(\'table\')')
            ->assertContains('public function down(): QueryStatement');
    }
}