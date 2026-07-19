<?php

declare(strict_types=1);

namespace Tempest\Database;

use function Tempest\Support\str;

trait HasTableAlias
{
    public bool $withPropertyNameAlias = false;

    public function withPropertyNameAlias(): self
    {
        $this->withPropertyNameAlias = true;

        return $this;
    }

    private function getTableAlias(string $tableName): string
    {
        if ($this->parent === null) {
            return $tableName;
        }

        if ($this->parent === '') {
            return $this->withPropertyNameAlias
                ? str(string: $this->property->getName())->wrap('`')->toString()
                : $tableName;
        }

        return str(string: $this->parent)
            ->replace(
                search: '.',
                replace: '_',
            )
            ->append(
                '_',
                $this->property->getName(),
            )
            ->wrap('`')
            ->toString();
    }

    private function quoteIdentifier(string $identifier): string
    {
        $parts = explode('.', $identifier);

        foreach ($parts as &$part) {
            $part = sprintf('`%s`', trim($part, '`" '));
        }

        return implode('.', $parts);
    }

    private function qualifyIdentifier(string $identifier, string $table): string
    {
        if (! str_contains($identifier, '.')) {
            $identifier = "{$table}.{$identifier}";
        }

        return $this->quoteIdentifier($identifier);
    }

    private function quoteTableReference(string $table, ?string $alias = null): string
    {
        $quotedTable = $this->quoteIdentifier($table);

        if ($alias === null || trim($alias, '`" ') === $table) {
            return $quotedTable;
        }

        return sprintf('%s AS %s', $quotedTable, $this->quoteIdentifier($alias));
    }

    private function replaceTableReference(string $qualifiedColumn, string $originalTable, string $aliasedTable): string
    {
        if ($aliasedTable === $originalTable) {
            return $qualifiedColumn;
        }

        return str(string: $qualifiedColumn)
            ->replaceFirst(
                search: "{$originalTable}.",
                replace: "{$aliasedTable}.",
            )
            ->toString();
    }
}
