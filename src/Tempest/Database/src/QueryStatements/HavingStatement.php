<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class HavingStatement implements QueryStatement
{
    public function __construct(
        private string $having,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return $this->having;
    }
}
