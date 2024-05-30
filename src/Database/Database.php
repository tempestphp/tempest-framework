<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Transactions\TransactionManager;

interface Database
{
    public function execute(Query $query): void;

    public function getLastInsertId(): Id;

    public function fetch(Query $query): array;

    public function fetchFirst(Query $query): ?array;

    public function withinTransaction(callable $callback): bool;
}
