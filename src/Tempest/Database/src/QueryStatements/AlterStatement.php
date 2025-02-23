<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class AlterStatement implements QueryStatement
{
    public function __construct(
        private Alter $type,
        private QueryStatement $statement,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            '%s %s',
            $this->type->compile($dialect),
            $this->statement->compile($dialect),
        );
    }
}
