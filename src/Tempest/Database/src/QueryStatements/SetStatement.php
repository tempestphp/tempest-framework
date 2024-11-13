<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\Exceptions\InvalidValue;
use Tempest\Database\QueryStatement;

final readonly class SetStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private array $values,
        private bool $nullable = false,
        private ?string $default = null,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        if (empty($this->values)) {
            throw new InvalidValue($this->name, json_encode($this->values));
        }

        return match($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                '`%s` SET (%s) %s %s',
                $this->name,
                "'" . implode(',', ${$this}->values) . "'",
                $this->default ? "DEFAULT '$this->default'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => ''
        };
    }
}
