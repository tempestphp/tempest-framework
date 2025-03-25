<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class JoinStatement implements QueryStatement
{
    public function __construct(
        private string $join,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return $this->join;
    }
}