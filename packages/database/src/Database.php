<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\QueryBuilders\BuildsQuery;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Support\Str\ImmutableString;
use UnitEnum;

/**
 * Represents a database that can execute queries.
 */
interface Database
{
    /**
     * The dialect of this database.
     */
    public DatabaseDialect $dialect {
        get;
    }

    /**
     * The tag associated with this database, if any.
     */
    public null|string|UnitEnum $tag {
        get;
    }

    /**
     * Executes the given query.
     */
    public function execute(BuildsQuery|Query $query): void;

    /**
     * Returns the last inserted primary key, if any.
     */
    public function getLastInsertId(): ?PrimaryKey;

    /**
     * Fetches all results for the given query.
     */
    public function fetch(BuildsQuery|Query $query): array;

    /**
     * Fetches the first result for the given query.
     */
    public function fetchFirst(BuildsQuery|Query $query): ?array;

    /**
     * Executes the given callback within a transaction.
     */
    public function withinTransaction(callable $callback): bool;

    /**
     * Returns the raw SQL representation of the given query for debugging purposes.
     */
    public function getRawSql(Query $query): ImmutableString;
}
