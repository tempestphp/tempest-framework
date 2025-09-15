<?php

namespace Tempest\Upgrade\Tests\Tempest2;

use PHPUnit\Framework\TestCase;
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
            ->assertNotContains('Tempest\Database\DatabaseMigration')
            ->assertContains('public function down(): QueryStatement');
    }

    public function test_database_id_rename(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/Model.input.php')
            ->assertContains('public PrimaryKey $id')
            ->assertContains('return $this->id->value;')
            ->assertContains('Tempest\Database\PrimaryKey')
            ->assertNotContains('Id')
            ->assertNotContains('Tempest\Database\Id');
    }

    public function test_uri_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/UriNamespaceChange.input.php')
            ->assertContains('use function Tempest\Router\uri;')
            ->assertNotContains('use function Tempest\uri;');
    }

    public function test_is_current_uri_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/IsCurrentUriNamespaceChange.input.php')
            ->assertContains('use function Tempest\Router\is_current_uri;')
            ->assertNotContains('use function Tempest\is_current_uri;');
    }
}
