<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

final readonly class TableName
{
    public function __construct(
        public string $tableName,
    ) {
    }

    public function __toString(): string
    {
        return $this->tableName;
    }
}
