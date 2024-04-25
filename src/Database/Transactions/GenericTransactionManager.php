<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

use PDO;
use Tempest\Database\Exceptions\TransactionException;
use Throwable;

final class GenericTransactionManager implements TransactionManager
{
    public function __construct(private PDO $pdo)
    {
    }

    public function begin(): void
    {
        $transactionBegun = $this->pdo->beginTransaction();

        if (! $transactionBegun) {
            throw TransactionException::beginFailed();
        }
    }

    public function commit(): void
    {
        $transactionCommitted = $this->pdo->commit();

        if (! $transactionCommitted) {
            throw TransactionException::commitFailed();
        }
    }

    public function rollback(): void
    {
        $transactionRolledBack = $this->pdo->rollBack();

        if (! $transactionRolledBack) {
            throw TransactionException::transactionFailed();
        }
    }

    public function execute(callable $callback): bool
    {
        $this->begin();

        try {
            $callback();

            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();

            return false;
        }

        return true;
    }
}
