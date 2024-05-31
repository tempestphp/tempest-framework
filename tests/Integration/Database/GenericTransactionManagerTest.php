<?php

declare(strict_types=1);

namespace Integration\Database;

use PDOException;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Transactions\TransactionManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class GenericTransactionManagerTest extends FrameworkIntegrationTestCase
{
    public function test_transaction_manager(): void
    {
        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        $this->console
            ->call('migrate:up');

        $this->assertCount(3, Migration::all());

        $manager->rollback();

        $this->expectException(PDOException::class); // Migration::all() will throw since the table doesn't exist

        $this->assertCount(0, Migration::all());
    }

    public function test_transaction_manager_rollback(): void
    {
        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        $this->console
            ->call('migrate:up');

        $this->assertCount(3, Migration::all());

        $manager->rollback();

        $this->expectException(PDOException::class); // Migration::all() will throw since the table doesn't exist

        $this->assertCount(0, Migration::all());
    }

    public function test_transaction_manager_commit(): void
    {
        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        $this->console
            ->call('migrate:up');

        $this->assertCount(3, Migration::all());

        $manager->commit();

        $this->assertCount(3, Migration::all());
    }

    public function test_transaction_manager_commit_rollback(): void
    {
        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        $this->console
            ->call('migrate:up');

        $this->assertCount(3, Migration::all());

        $manager->commit();

        $this->assertCount(3, Migration::all());

        $this->expectException(PDOException::class); // Migration::all() will throw since the table doesn't exist

        $manager->rollback();

        $this->assertCount(3, Migration::all());
    }

    public function test_transaction_manager_rollback_commit(): void
    {
        $manager = $this->container->get(TransactionManager::class);

        $manager->begin();

        $this->console
            ->call('migrate:up');

        $this->assertCount(3, Migration::all());

        $manager->rollback();

        $this->expectException(PDOException::class); // Migration::all() will throw since the table doesn't exist

        $this->assertCount(0, Migration::all());

        $manager->commit();

        $this->assertCount(0, Migration::all());
    }
}
