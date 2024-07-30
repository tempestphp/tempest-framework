<?php

namespace Tempest\Database\Builder\Relations;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use Tempest\Support\Reflection\ClassReflector;

final readonly class BelongsToRelation implements Relation
{
    private string $statement;

    private function __construct(
        private string $relationName,
        private ClassReflector $modelClass,
        private TableName $joinTable,
        FieldName $onField,
        FieldName $isField,
    ) {
        $this->statement = sprintf(
            'INNER JOIN %s ON %s = %s',
            $joinTable,
            $onField,
            $isField,
        );
    }

    public static function make(ClassReflector $modelClass, string $relation): array
    {
        $relations = [];
        $relationNames = explode('.', $relation);
        $localModelClass = $modelClass;
        $loopedParts = [TableName::for($localModelClass)->tableName];

        foreach ($relationNames as $relationNamePart) {
            $property = $localModelClass->getProperty($relationNamePart);
            $loopedParts[] = $property->getName();

            $relationName = implode('.', $loopedParts);

            $localTable = TableName::for($localModelClass);
            $joinTable = TableName::for($property->getType()->asClass(), $relationName);

            $localModelClass = $property->getType()->asClass();

            $relations[] = new self(
                relationName: $relationName,
                modelClass: $localModelClass,
                joinTable: $joinTable,
                onField: new FieldName($localTable, $property->getName() . '_id'),
                isField: new FieldName($joinTable, 'id'),
            );
        }

        return $relations;
    }

    public function getStatement(): string
    {
        return $this->statement;
    }

    public function getRelationName(): string
    {
        return $this->relationName;
    }

    public function getFieldNames(): array
    {
        $fieldNames = [];

        foreach ($this->modelClass->getPublicProperties() as $property) {
            $fieldNames[] = new FieldName($this->joinTable, $property->getName());
        }

        return $fieldNames;
    }
}