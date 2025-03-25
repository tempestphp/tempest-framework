<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class OrderByStatement implements QueryStatement
{
    public function __construct(
        private string $orderBy,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return $this->orderBy;
    }
}