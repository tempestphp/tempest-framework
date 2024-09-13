<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use Tempest\Support\Reflection\ClassReflector;
use Tempest\Support\Reflection\PropertyReflector;

final readonly class HasManyRelation implements Relation
{
    private ClassReflector $relationModelClass;

    private FieldName $localField;

    private FieldName $joinField;

    public function __construct(PropertyReflector $property, string $alias)
    {
        $this->relationModelClass = $property->getIterableType()->asClass();

        $inverseProperty = null;

        foreach ($this->relationModelClass->getPublicProperties() as $potentialInverseProperty) {
            if ($potentialInverseProperty->getType()->equals($property->getClass()->getType())) {
                $inverseProperty = $potentialInverseProperty;

                break;
            }
        }

        $localTable = TableName::for($property->getClass(), $alias);
        $this->localField = new FieldName($localTable, 'id');

        $joinTable = TableName::for($this->relationModelClass, "{$alias}.{$property->getName()}[]");
        $this->joinField = new FieldName($joinTable, $inverseProperty->getName() . '_id');
    }

    public function getStatement(): string
    {
        return sprintf(
            'INNER JOIN %s ON %s = %s',
            $this->joinField->tableName,
            $this->localField,
            $this->joinField,
        );
    }

    public function getRelationName(): string
    {
        return $this->joinField->tableName->as;
    }

    public function getFieldNames(): array
    {
        return FieldName::make($this->relationModelClass, $this->joinField->tableName);
    }
}
