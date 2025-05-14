<?php

namespace Tempest\Database\QueryStatements;

use Stringable;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

use function Tempest\Support\arr;

final readonly class FieldStatement implements QueryStatement
{
    public function __construct(
        private string|Stringable $field,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $parts = explode(' AS ', str_replace(' as ', ' AS ', $this->field));

        if (count($parts) === 1) {
            $field = $parts[0];
            $alias = null;
        } else {
            $field = $parts[0];
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
}
