<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PDOException;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Transactions\TransactionManager;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericTransactionManagerTest extends FrameworkIntegrationTestCase
{
    public function test_transaction_manager(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateAuthorTable::class);

        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        (new Author(name: 'test'))->save();
        $this->assertCount(1, Author::all());

        $manager->rollback();

        $this->assertCount(0, Author::all());
    }

    public function test_transaction_manager_commit(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateAuthorTable::class);

        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        (new Author(name: 'test'))->save();
        $this->assertCount(1, Author::all());

        $manager->commit();

        $this->assertCount(1, Author::all());
    }

    public function test_transaction_manager_commit_rollback(): void
    {

        $this->migrate(CreateMigrationsTable::class, CreateAuthorTable::class);

        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        (new Author(name: 'test'))->save();

        $manager->commit();

        $this->expectException(PDOException::class);

        $manager->rollback();
    }
}
