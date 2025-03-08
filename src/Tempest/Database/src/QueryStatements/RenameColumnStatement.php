<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class RenameColumnStatement implements QueryStatement
{
    private IdentityStatement $from;

    private IdentityStatement $to;

    public function __construct(string $from, string $to)
    {
        $this->from = new IdentityStatement($from);
        $this->to = new IdentityStatement($to);
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
