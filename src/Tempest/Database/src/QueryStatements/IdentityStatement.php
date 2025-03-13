<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class IdentityStatement implements QueryStatement
{
    public function __construct(
        public string $name,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf('`%s`', $this->name);
    }
}
