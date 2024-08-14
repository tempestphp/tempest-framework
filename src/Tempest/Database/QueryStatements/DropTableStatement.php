<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class DropTableStatement implements QueryStatement
{
    use CanExecuteStatement;

    public function __construct(
        private string $tableName
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf('DROP TABLE %s', $this->tableName);
    }
}
