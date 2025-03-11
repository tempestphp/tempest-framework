<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class AlterDropStatement implements QueryStatement
{
    public function __construct(
        private ColumnNameStatement|ConstraintNameStatement $toDrop,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf('DROP %s', $this->toDrop->compile($dialect));
    }
}
