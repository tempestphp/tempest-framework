<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class RawStatement implements QueryStatement
{
    public function __construct(
        private string $statement,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return $this->statement;
    }
}
