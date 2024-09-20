<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Exceptions\CouldNotBeginTransaction;
use Tempest\Database\Exceptions\CouldNotCommitTransaction;
use Tempest\Database\Exceptions\CouldNotRollbackTransaction;
use Tempest\Database\Transactions\GenericTransactionManager;

/**
 * @internal
 */
final class GenericTransactionManagerTest extends TestCase
{
    public function test_it_calls_being_transactions(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('beginTransaction')
            ->withAnyParameters()
            ->willReturn(true);

        $manager = new GenericTransactionManager($pdo);

        $manager->begin();
    }

    public function test_it_throws_an_exception_when_transaction_cannot_begin(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('beginTransaction')
            ->withAnyParameters()
            ->willReturn(false);

        $this->expectException(CouldNotBeginTransaction::class);

        $manager = new GenericTransactionManager($pdo);

        $manager->begin();
    }

    public function test_it_calls_commit_transactions(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('commit')
            ->withAnyParameters()
            ->willReturn(true);

        $manager = new GenericTransactionManager($pdo);

        $manager->commit();
    }

    public function test_it_throws_an_exception_when_transaction_cannot_commit(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('commit')
            ->withAnyParameters()
            ->willReturn(false);

        $this->expectException(CouldNotCommitTransaction::class);

        $manager = new GenericTransactionManager($pdo);

        $manager->commit();
    }

    public function test_it_calls_rollback_transactions(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('rollBack')
            ->withAnyParameters()
            ->willReturn(true);

        $manager = new GenericTransactionManager($pdo);

        $manager->rollback();
    }

    public function test_it_throws_an_exception_when_transaction_cannot_rollback(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('rollBack')
            ->withAnyParameters()
            ->willReturn(false);

        $this->expectException(CouldNotRollbackTransaction::class);

        $manager = new GenericTransactionManager($pdo);

        $manager->rollback();
    }
}
