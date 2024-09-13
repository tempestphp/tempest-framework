<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

use PDO;
use Tempest\Database\Exceptions\CouldNotBeginTransaction;
use Tempest\Database\Exceptions\CouldNotCommitTransaction;
use Tempest\Database\Exceptions\CouldNotRollbackTransaction;

final class GenericTransactionManager implements TransactionManager
{
    public function __construct(private PDO $pdo)
    {
    }

    public function begin(): void
    {
        $transactionBegun = $this->pdo->beginTransaction();

        if (! $transactionBegun) {
            throw new CouldNotBeginTransaction();
        }
    }

    public function commit(): void
    {
        $transactionCommitted = $this->pdo->commit();

        if (! $transactionCommitted) {
            throw new CouldNotCommitTransaction();
        }
    }

    public function rollback(): void
    {
        $transactionRolledBack = $this->pdo->rollBack();

        if (! $transactionRolledBack) {
            throw new CouldNotRollbackTransaction();
        }
    }
}
