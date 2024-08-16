<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class DatetimeStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            '%s DATETIME %s',
            $this->name,
            $this->nullable ? '' : 'NOT NULL',
        );
    }
}
