<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class TextStatement implements QueryStatement
{
    public function __construct(
        private string $columnName,
        private bool $nullable = false,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            '%s TEXT %s',
            $this->columnName,
            $this->nullable ? '' : 'NOT NULL',
        );
    }
}
