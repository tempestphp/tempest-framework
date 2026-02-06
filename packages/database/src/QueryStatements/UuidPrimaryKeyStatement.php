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
        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf('`%s` VARCHAR(36) PRIMARY KEY', $this->name),
            DatabaseDialect::POSTGRESQL => sprintf('`%s` UUID PRIMARY KEY', $this->name),
            DatabaseDialect::SQLITE => sprintf('`%s` TEXT PRIMARY KEY', $this->name),
        };
    }
}
