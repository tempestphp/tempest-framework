<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use InvalidArgumentException;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class DatetimeStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
        private ?string $default = null,
        private bool $current = false,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->default !== null && $this->current) {
            throw new InvalidArgumentException("Cannot set both `default` and `current` for datetime column `{$this->name}`.");
        }

        $default = match (true) {
            $this->current => 'CURRENT_TIMESTAMP',
            $this->default !== null => "'{$this->default}'",
            default => null,
        };

        $name = $dialect->quoteIdentifier($this->name);

        return match ($dialect) {
            DatabaseDialect::POSTGRESQL => sprintf(
                '%s TIMESTAMP %s %s',
                $name,
                $default !== null ? "DEFAULT {$default}" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => sprintf(
                '%s DATETIME %s %s',
                $name,
                $default !== null ? "DEFAULT {$default}" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
        };
    }
}
