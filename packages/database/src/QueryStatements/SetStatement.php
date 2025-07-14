<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\DialectWasNotSupported;
use Tempest\Database\Exceptions\ValueWasInvalid;
use Tempest\Database\QueryStatement;
use Tempest\Support\Json;

final readonly class SetStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private array $values,
        private bool $nullable = false,
        private ?string $default = null,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->values === []) {
            throw new ValueWasInvalid($this->name, Json\encode($this->values));
        }

        return match ($dialect) {
            DatabaseDialect::MYSQL => sprintf(
                '`%s` SET (%s) %s %s',
                $this->name,
                "'" . implode("', '", $this->values) . "'",
                $this->default ? "DEFAULT '{$this->default}'" : '',
                $this->nullable ? '' : 'NOT NULL',
            ),
            default => throw new DialectWasNotSupported(),
        };
    }
}
