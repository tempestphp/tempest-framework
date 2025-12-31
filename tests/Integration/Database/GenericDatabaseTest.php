<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Exception;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Publisher;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class GenericDatabaseTest extends FrameworkIntegrationTestCase
{
    public function test_transaction_manager_execute(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        $db = $this->container->get(Database::class);

        $db->withinTransaction(function (): void {
            query(Author::class)
                ->insert(
                    name: 'Brent',
                )
                ->execute();
        });

        $this->assertSame(1, query(Author::class)->count()->execute());
    }

    public function test_transaction_manager_fails(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        $db = $this->container->get(Database::class);

        $db->withinTransaction(function (): never {
            query(Author::class)
                ->insert(
                    name: 'Brent',
                )
                ->execute();

            throw new Exception('Test');
        });

        $this->assertSame(0, query(Author::class)->count()->execute());
    }

    public function test_query_with_semicolons(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class);

        $db = $this->container->get(Database::class);
        $db->execute(
            new Query(<<<SQL
                INSERT INTO publishers (`name`, `description`)
                VALUES ('Foo', 'Bar; Baz;')
            SQL),
        );

        $this->assertSame(1, query(Publisher::class)->count()->execute());
    }

    public function test_query_was_invalid_exception_is_thrown_on_fetch(): void
    {
        $this->assertException(QueryWasInvalid::class, function (): void {
            query('books')->select()->orderByRaw('title DES')->first();
        });
    }

    public function test_query_was_invalid_exception_is_thrown_on_execute(): void
    {
        $this->assertException(QueryWasInvalid::class, function (): void {
            query('books')->update(title: 'Timeline Taxi')->whereRaw('title = ?')->execute();
        });
    }
}
