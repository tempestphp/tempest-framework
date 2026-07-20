<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PDOException;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Transactions\TransactionManager;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericTransactionManagerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function transaction_manager(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        new Author(name: 'test')->save();
        $this->assertCount(1, Author::all());

        $manager->rollback();

        $this->assertCount(0, Author::all());
    }

    #[Test]
    public function transaction_manager_commit(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        new Author(name: 'test')->save();
        $this->assertCount(1, Author::all());

        $manager->commit();

        $this->assertCount(1, Author::all());
    }

    #[Test]
    public function transaction_manager_commit_rollback(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        new Author(name: 'test')->save();

        $manager->commit();

        $this->expectException(PDOException::class);

        $manager->rollback();
    }
}
