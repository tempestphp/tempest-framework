<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class IntegerStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $unsigned = false,
        private bool $nullable = false,
        private ?int $default = null,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            '`%s` INTEGER %s %s %s',
            $this->name,
            $this->unsigned ? 'UNSIGNED' : '',
            $this->default ? "DEFAULT {$this->default}" : '',
            $this->nullable ? '' : 'NOT NULL',
        );
    }
}
