<?php

namespace Tempest\Database\QueryStatements;

use Stringable;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

use function Tempest\Support\arr;

final class FieldStatement implements QueryStatement
{
    private bool $withAlias = false;

    public function __construct(
        private readonly string|Stringable $field,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $parts = explode(' AS ', str_replace(' as ', ' AS ', $this->field));

        $field = $parts[0];

        if (count($parts) === 1) {
            $alias = null;

            if ($this->withAlias) {
                $alias = sprintf(
                    '`%s`',
                    str_replace('`', '', $field),
                );
            }
        } else {
            $alias = $parts[1];
        }

        $field = arr(explode('.', $field))
            ->map(fn (string $part) => trim($part, '` '))
            ->map(fn (string $part) => match ($dialect) {
                DatabaseDialect::SQLITE => $part,
                default => sprintf('`%s`', $part),
            })
            ->implode('.');

        if ($alias === null) {
            return $field;
        }

        return sprintf('%s AS `%s`', $field, trim($alias, '`'));
    }

    public function withAlias(): self
    {
        $this->withAlias = true;

        return $this;
    }
}
