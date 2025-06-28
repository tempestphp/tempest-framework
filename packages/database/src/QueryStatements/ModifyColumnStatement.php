<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\DialectWasNotSupported;
use Tempest\Database\QueryStatement;

final readonly class ModifyColumnStatement implements QueryStatement
{
    public function __construct(
        private QueryStatement $column,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf('MODIFY COLUMN %s', $this->column->compile($dialect)),
            DatabaseDialect::POSTGRESQL => sprintf('ALTER COLUMN %s', $this->column->compile($dialect)),
            DatabaseDialect::SQLITE => throw new DialectWasNotSupported(),
        };
    }
}
