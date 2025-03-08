<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class ColumnNameStatement implements QueryStatement
{
    private IdentityStatement $identity;

    public function __construct(string $name)
    {
        $this->identity = new IdentityStatement($name);
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf('COLUMN %s', $this->identity->compile($dialect));
    }
}
