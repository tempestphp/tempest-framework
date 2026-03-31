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
