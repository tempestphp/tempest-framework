<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class UniqueStatement implements QueryStatement
{
    public function __construct(
        private string $columnName,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf('UNIQUE (%s)', $this->columnName);
    }
}
