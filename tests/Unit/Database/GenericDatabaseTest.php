<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Database;

use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Transactions\GenericTransactionManager;

/**
 * @internal
 * @small
 */
final class GenericDatabaseTest extends TestCase
{
    public function test_it_executes_transactions(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('beginTransaction')
            ->withAnyParameters()
            ->willReturn(true);
        $pdo->expects($this->once())
            ->method('commit')
            ->withAnyParameters()
            ->willReturn(true);

        $database = new GenericDatabase(
            $pdo,
            new GenericTransactionManager($pdo)
        );

        $result = $database->withinTransaction(function () {
            return true;
        });

        $this->assertTrue($result);
    }

    public function test_it_rolls_back_transactions_on_failure(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('beginTransaction')
            ->withAnyParameters()
            ->willReturn(true);
        $pdo->expects($this->once())
            ->method('rollback')
            ->withAnyParameters()
            ->willReturn(true);

        $database = new GenericDatabase(
            $pdo,
            new GenericTransactionManager($pdo)
        );

        $result = $database->withinTransaction(function () {
            throw new Exception();
        });

        $this->assertFalse($result);
    }
}
