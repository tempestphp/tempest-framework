<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class GroupByStatement implements QueryStatement
{
    public function __construct(
        private string $groupBy,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return $this->groupBy;
    }
}
