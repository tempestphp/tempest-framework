<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class DatetimeStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
        private ?string $default = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        return match($dialect){
            DatabaseDialect::POSTGRESQL => sprintf(
                '`%s` TIMESTAMP %s %s',
                $this->name,
                $this->default !== null ? "DEFAULT '{$this->default}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => sprintf(
                '`%s` DATETIME %s %s',
                $this->name,
                $this->default !== null ? "DEFAULT \"{$this->default}\"" : '',
                $this->nullable ? '' : 'NOT NULL',
            )
        };
    }
}
