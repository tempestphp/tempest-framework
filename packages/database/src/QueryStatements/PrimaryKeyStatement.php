<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class PrimaryKeyStatement implements QueryStatement
{
    public function __construct(
        private string $name = 'id',
        private bool $unsigned = false,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $name = $dialect->quoteIdentifier($this->name);

        return match ($dialect) {
            DatabaseDialect::MYSQL => $this->unsigned
                ? "{$name} INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT"
                : "{$name} INTEGER PRIMARY KEY AUTO_INCREMENT",
            // Postgres and SQLite have no unsigned integer type, so omit the keyword there.
            DatabaseDialect::POSTGRESQL => "{$name} SERIAL PRIMARY KEY",
            DatabaseDialect::SQLITE => "{$name} INTEGER PRIMARY KEY AUTOINCREMENT",
        };
    }
}
