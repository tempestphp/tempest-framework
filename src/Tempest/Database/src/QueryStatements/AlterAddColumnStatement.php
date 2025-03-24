<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class AlterAddColumnStatement implements QueryStatement
{
    public function __construct(
        private QueryStatement $statement,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf('ADD %s', $this->statement->compile($dialect));
    }
}
