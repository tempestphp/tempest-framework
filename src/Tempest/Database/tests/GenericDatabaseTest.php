<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Transactions\GenericTransactionManager;

/**
 * @internal
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

        $result = $database->withinTransaction(function (): never {
            throw new Exception();
        });

        $this->assertFalse($result);
    }
}
