<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Database\UnsupportedDialect;

final readonly class ConstraintNameStatement implements QueryStatement
{
    private IdentityStatement $identity;

    public function __construct(string $name)
    {
        $this->identity = new IdentityStatement($name);
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL, DatabaseDialect::POSTGRESQL => sprintf('CONSTRAINT %s', $this->identity->compile($dialect)),
            default => throw new UnsupportedDialect(),
        };
    }
}
