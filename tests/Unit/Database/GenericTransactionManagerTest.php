<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Database;

use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Exceptions\CannotBeginTransaction;
use Tempest\Database\Exceptions\CannotCommitTransaction;
use Tempest\Database\Exceptions\CannotRollbackTransaction;
use Tempest\Database\Transactions\GenericTransactionManager;

/**
 * @internal
 * @small
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

        $this->expectException(CannotBeginTransaction::class);

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

        $this->expectException(CannotCommitTransaction::class);

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

        $this->expectException(CannotRollbackTransaction::class);

        $manager = new GenericTransactionManager($pdo);

        $manager->rollback();
    }

}
