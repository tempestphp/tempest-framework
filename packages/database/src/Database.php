<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\QueryBuilders\BuildsQuery;
use Tempest\Database\Config\DatabaseDialect;

interface Database
{
    public DatabaseDialect $dialect {
        get;
    }

    public function execute(BuildsQuery|Query $query): void;

    public function getLastInsertId(): ?Id;

    public function fetch(BuildsQuery|Query $query): array;

    public function fetchFirst(BuildsQuery|Query $query): ?array;

    public function withinTransaction(callable $callback): bool;
}
