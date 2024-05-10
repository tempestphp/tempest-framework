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

    public function asDefault(): self
    {
        return $this->as($this->tableName->tableName . ':' . $this->fieldName);
    }

    public function __toString(): string
    {
        $string = "{$this->tableName}.`{$this->fieldName}`";

        if ($this->as) {
            $string .= " AS `{$this->as}`";
        }

        return $string;
    }
}
