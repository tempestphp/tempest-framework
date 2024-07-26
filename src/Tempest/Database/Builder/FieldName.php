<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

final class FieldName
{
    public function __construct(
        public readonly TableName $tableName,
        public readonly string $fieldName,
        public ?string $as = null
    ) {
    }

    public function as(string $as): self
    {
        $this->as = $as;

        return $this;
    }

    public function withAlias(): self
    {
        $tableName = $this->tableName->as ?? $this->tableName->tableName;

        return $this->as($tableName . '.' . $this->fieldName);
    }

    public function __toString(): string
    {
        $tableName = $this->tableName->as ?? $this->tableName->tableName;

        $string = "`{$tableName}`.`{$this->fieldName}`";

        if ($this->as) {
            $string .= " AS `{$this->as}`";
        }

        return $string;
    }
}
