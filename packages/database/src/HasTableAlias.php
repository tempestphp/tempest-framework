<?php

declare(strict_types=1);

namespace Tempest\Database;

use function Tempest\Support\str;

trait HasTableAlias
{
    private function getTableAlias(string $tableName): string
    {
        if ($this->parent === null || $this->parent === '') {
            return $tableName;
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
}
