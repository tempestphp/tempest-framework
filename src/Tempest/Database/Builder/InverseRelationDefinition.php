<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

readonly class InverseRelationDefinition
{
    public function __construct(
        /** @var class-string<\Tempest\Database\Model> $modelClass */
        private string $modelClass,
        private string $fieldName,
    ) {
    }

    public function getTableName(): TableName
    {
        return ($this->modelClass)::table();
    }

    public function getFieldName(): FieldName
    {
        return new FieldName(
            tableName: $this->getTableName(),
            fieldName: $this->fieldName,
        );
    }
}
