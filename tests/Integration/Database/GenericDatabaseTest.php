<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Exception;
use PDOException;
use Tempest\Database\Database;
use Tempest\Database\Migrations\Migration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
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

        $this->assertCount(3, Migration::all());
    }

    public function test_execute_with_fail_works_correctly(): void
    {
        $manager = $this->container->get(Database::class);

        $manager->withinTransaction(function (): never {
            $this->console
                ->call('migrate:up');

            throw new Exception("Dummy exception to force rollback");
        });

        $this->expectException(PDOException::class); // Migration::all() will throw since the table doesn't exist

        $this->assertCount(0, Migration::all());
    }
}
