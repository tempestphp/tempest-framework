<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

/**
 * A UUID column that is not a primary key. Uses `CHAR(36)` for MySQL, `UUID` for PostgreSQL, and `TEXT` for SQLite.
 */
final readonly class UuidStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $name = $dialect->quoteIdentifier($this->name);
        $nullable = $this->nullable ? '' : ' NOT NULL';

        return match ($dialect) {
            DatabaseDialect::MYSQL => "{$name} CHAR(36){$nullable}",
            DatabaseDialect::POSTGRESQL => "{$name} UUID{$nullable}",
            DatabaseDialect::SQLITE => "{$name} TEXT{$nullable}",
        };
    }
}
