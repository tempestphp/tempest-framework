<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Exception;
use Tempest\Database\Database;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Database\query;

/**
 * @internal
 */
final class GenericDatabaseTest extends FrameworkIntegrationTestCase
{
    public function test_transaction_manager_execute(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        $db = $this->container->get(Database::class);

        $db->withinTransaction(function (): void {
            query(Author::class)->insert(
                name: 'Brent',
            )->execute();
        });

        $this->assertSame(1, query(Author::class)->count()->execute());
    }

    public function test_transaction_manager_fails(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        $db = $this->container->get(Database::class);

        $db->withinTransaction(function (): void {
            query(Author::class)->insert(
                name: 'Brent',
            )->execute();

            throw new Exception('Test');
        });

        $this->assertSame(0, query(Author::class)->count()->execute());
    }
}
