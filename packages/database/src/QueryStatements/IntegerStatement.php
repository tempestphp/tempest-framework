<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class IntegerStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $unsigned = false,
        private bool $nullable = false,
        private int|DatabaseIntegerSize $size = DatabaseIntegerSize::DEFAULT,
        private ?int $default = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $name = $dialect->quoteIdentifier($this->name);

        return match ($dialect) {
            DatabaseDialect::SQLITE => sprintf(
                '%s INTEGER %s %s %s',
                $name,
                $this->unsigned ? 'UNSIGNED' : '',
                $this->default !== null ? "DEFAULT {$this->default}" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => sprintf(
                '%s %s %s %s %s',
                $name,
                is_int($this->size) ? DatabaseIntegerSize::fromBytes($this->size)->toString() : $this->size->toString(),
                $this->unsigned ? 'UNSIGNED' : '',
                $this->default !== null ? "DEFAULT {$this->default}" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
        };
    }
}
