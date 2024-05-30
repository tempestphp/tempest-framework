<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

interface TransactionManager
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;
}
