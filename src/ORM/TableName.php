<?php

namespace Tempest\ORM;

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
