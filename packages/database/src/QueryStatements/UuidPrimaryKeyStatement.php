<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class UuidPrimaryKeyStatement implements QueryStatement
{
    public function __construct(
        private string $name = 'id',
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $name = $dialect->quoteIdentifier($this->name);

        return match ($dialect) {
            DatabaseDialect::MYSQL => "{$name} CHAR(36) PRIMARY KEY",
            DatabaseDialect::POSTGRESQL => "{$name} UUID PRIMARY KEY",
            DatabaseDialect::SQLITE => "{$name} TEXT PRIMARY KEY",
        };
    }
}
