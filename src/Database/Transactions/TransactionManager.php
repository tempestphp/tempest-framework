<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

use PDO;

interface TransactionManager
{
    public function __construct(PDO $pdo);

    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;

    public function execute(callable $callback): bool;
}
