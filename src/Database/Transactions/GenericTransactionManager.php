<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

use PDO;
use Tempest\Database\Exceptions\CannotBeginTransaction;
use Tempest\Database\Exceptions\CannotCommitTransaction;
use Tempest\Database\Exceptions\CannotRollbackTransaction;

final class GenericTransactionManager implements TransactionManager
{
    public function __construct(private PDO $pdo)
    {
    }

    public function begin(): void
    {
        $transactionBegun = $this->pdo->beginTransaction();

        if (! $transactionBegun) {
            throw new CannotBeginTransaction();
        }
    }

    public function commit(): void
    {
        $transactionCommitted = $this->pdo->commit();

        if (! $transactionCommitted) {
            throw new CannotCommitTransaction();
        }
    }

    public function rollback(): void
    {
        $transactionRolledBack = $this->pdo->rollBack();

        if (! $transactionRolledBack) {
            throw new CannotRollbackTransaction();
        }
    }
}
