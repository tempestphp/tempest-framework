<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class PrimaryKeyStatement implements QueryStatement
{
    public function __construct(
        private string $name = 'id',
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf('`%s` INTEGER PRIMARY KEY AUTO_INCREMENT', $this->name),
            DatabaseDialect::POSTGRESQL => sprintf('`%s` SERIAL PRIMARY KEY', $this->name),
            DatabaseDialect::SQLITE => sprintf('`%s` INTEGER PRIMARY KEY AUTOINCREMENT', $this->name),
        };
    }
}
