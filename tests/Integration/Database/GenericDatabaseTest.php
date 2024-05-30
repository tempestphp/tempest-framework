<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Exception;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Transactions\TransactionManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class GenericDatabaseTest extends FrameworkIntegrationTestCase
{


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
