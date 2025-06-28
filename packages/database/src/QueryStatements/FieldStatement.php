<?php

namespace Tempest\Database\QueryStatements;

use Stringable;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

use function Tempest\Support\arr;

final class FieldStatement implements QueryStatement
{
    private null|bool|string $alias = null;
    private ?string $aliasPrefix = null;

    public function __construct(
        private readonly string|Stringable $field,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $parts = explode(' AS ', str_replace(' as ', ' AS ', $this->field));

        $field = $parts[0];

        if (count($parts) === 1) {
            $alias = null;
            $aliasPrefix = $this->aliasPrefix ? "{$this->aliasPrefix}." : '';

            if ($this->alias === true) {
                $alias = sprintf(
                    '`%s%s`',
                    $aliasPrefix,
                    str_replace('`', '', $field),
                );
            } elseif ($this->alias) {
                $alias = sprintf(
                    '`%s%s`',
                    $aliasPrefix,
                    $this->alias,
                );
            }
        } else {
            $alias = $parts[1];
        }

        $field = arr(explode('.', $field))
            ->map(fn (string $part) => trim($part, '` '))
            ->map(
                function (string $part) use ($dialect) {
                    // Function calls are never wrapped in backticks.
                    if (str_contains($part, '(')) {
                        return $part;
                    }

                    if ($dialect === DatabaseDialect::SQLITE) {
                        return $part;
                    }

                    return sprintf('`%s`', $part);
                },
            )
            ->implode('.');

        if ($alias === null) {
            return $field;
        }

        return match ($dialect) {
            DatabaseDialect::POSTGRESQL => sprintf('%s AS "%s"', $field, trim($alias, '`')),
            default => sprintf('%s AS `%s`', $field, trim($alias, '`')),
        };
    }

    public function withAliasPrefix(?string $prefix = null): self
    {
        $this->aliasPrefix = $prefix;

        return $this;
    }

    public function withAlias(bool|string $alias = true): self
    {
        $this->alias = $alias;

        return $this;
    }
}
