<?php

declare(strict_types=1);

namespace Integration\Database;

use Exception;
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

        $this->expectExceptionMessage("no such table");

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

        $this->expectExceptionMessage("no such table");

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

        $this->expectExceptionMessage("There is no active transaction");
        $this->expectException(PDOException::class);

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

        $this->expectExceptionMessage("no such table");

        $this->assertCount(0, Migration::all());

        $manager->commit();

        $this->assertCount(0, Migration::all());
    }

    public function test_transaction_manager_execute(): void
    {
        $manager = $this->container->get(TransactionManager::class);

        $manager->execute(function () {
            $this->console
                ->call('migrate:up');
        });

        $this->assertCount(3, Migration::all());
    }

    public function test_execute_with_fail_works_correctly(): void
    {
        $manager = $this->container->get(TransactionManager::class);

        $this->expectExceptionMessage("no such table");

        $manager->execute(function () {
            $this->console
                ->call('migrate:up');

            throw new Exception("no such table");
        });

        $this->assertCount(0, Migration::all());
    }
}
