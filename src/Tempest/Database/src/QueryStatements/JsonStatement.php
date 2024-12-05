<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\Exceptions\InvalidDefaultValue;
use Tempest\Database\QueryStatement;

final readonly class JsonStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
        private ?string $default = null,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->default && json_validate($this->default) === false) {
            throw new InvalidDefaultValue($this->name, $this->default);
        }

        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                '`%s` JSON %s',
                $this->name,
                $this->nullable ? '' : 'NOT NULL',
            ),
            DatabaseDialect::SQLITE => sprintf(
                '`%s` TEXT %s %s',
                $this->name,
                $this->default ? "DEFAULT '{$this->default}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            DatabaseDialect::POSTGRESQL => sprintf(
                '`%s` JSONB %s %s',
                $this->name,
                $this->default ? "DEFAULT (\"{$this->default}\")" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
        };
    }
}
