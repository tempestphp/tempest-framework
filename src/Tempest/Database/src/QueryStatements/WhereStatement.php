<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class WhereStatement implements QueryStatement
{
    public function __construct(
        private string $where,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return $this->where;
    }
}