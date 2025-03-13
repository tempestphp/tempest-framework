<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class RenameColumnStatement implements QueryStatement
{
    public function __construct(
        private IdentityStatement $from,
        private IdentityStatement $to,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            'RENAME COLUMN %s TO %s',
            $this->from->compile($dialect),
            $this->to->compile($dialect),
        );
    }
}
