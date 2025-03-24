<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Exception;
use Tempest\Database\Database;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Migrations\Migration;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericDatabaseTest extends FrameworkIntegrationTestCase
{
    public function test_transaction_manager_execute(): void
    {
        $manager = $this->container->get(Database::class);

        $manager->withinTransaction(function (): void {
            $this->console
                ->call('migrate:up');
        });

        $this->assertNotEmpty(Migration::all());
    }

    public function test_execute_with_fail_works_correctly(): void
    {
        $database = $this->container->get(Database::class);

        $this->migrate(CreateMigrationsTable::class, CreateAuthorTable::class);

        $database->withinTransaction(function (): never {
            new Author(name: 'test')->save();

            throw new Exception('Dummy exception to force rollback');
        });

        $this->assertCount(0, Author::all());
    }
}
