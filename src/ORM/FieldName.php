<?php

declare(strict_types=1);

namespace Tempest\ORM;

final readonly class FieldName
{
    public function __construct(
        public TableName $tableName,
        public string $fieldName,
    ) {
    }

    public function __toString(): string
    {
        return "{$this->tableName}.{$this->fieldName}";
    }
}
