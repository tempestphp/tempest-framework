<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class TextStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
        private ?string $default = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                '`%s` TEXT %s',
                $this->name,
                $this->nullable ? '' : 'NOT NULL',
            ),
            DatabaseDialect::POSTGRESQL => sprintf(
                '`%s` TEXT %s %s',
                $this->name,
                $this->default !== null ? "DEFAULT '{$this->default}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => sprintf(
                '`%s` TEXT %s %s',
                $this->name,
                $this->default !== null ? "DEFAULT \"{$this->default}\"" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
        };
    }
}
