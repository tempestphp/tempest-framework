<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class PrimaryKeyStatement implements QueryStatement
{
    public function __construct(
        private string $name = 'id',
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $name = $dialect->quoteIdentifier($this->name);

        return match ($dialect) {
            DatabaseDialect::MYSQL => "{$name} INTEGER PRIMARY KEY AUTO_INCREMENT",
            DatabaseDialect::POSTGRESQL => "{$name} SERIAL PRIMARY KEY",
            DatabaseDialect::SQLITE => "{$name} INTEGER PRIMARY KEY AUTOINCREMENT",
        };
    }
}
