<?php

declare(strict_types=1);

namespace Tempest\Database;

use function Tempest\Support\str;

trait HasTableAlias
{
    private function getTableAlias(string $tableName): string
    {
        if ($this->parent === null) {
            return $tableName;
        }

        if ($this->parent === '') {
            return $this->property->getName();
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
            ->toString();
    }

    private function getOwnerTableAlias(string $ownerTableName): string
    {
        if ($this->parent === null || $this->parent === '') {
            return $ownerTableName;
        }

        return str(string: $this->parent)
            ->replace(
                search: '.',
                replace: '_',
            )
            ->toString();
    }

    private function rewriteTablePrefix(string $qualifiedColumn, string $originalTable, string $aliasedTable): string
    {
        if ($aliasedTable === $originalTable) {
            return $qualifiedColumn;
        }

        return str(string: $qualifiedColumn)
            ->replaceFirst(
                search: $originalTable . '.',
                replace: $aliasedTable . '.',
            )
            ->toString();
    }
}
