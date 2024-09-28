<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;

final readonly class HasOneRelation implements Relation
{
    private ClassReflector $relationModelClass;

    private FieldName $localField;

    private FieldName $joinField;

    public function __construct(PropertyReflector $property, string $alias)
    {
        // TODO: optionally allow property name on the attribute so that we don't have to resolve it
        $inverseProperty = null;

        $currentModelClass = $property->getClass();

        if ($inverseProperty === null) {
            // TODO: handle reflection exceptions
            foreach ($property->getType()->asClass()->getPublicProperties() as $possibleInverseProperty) {
                if ($possibleInverseProperty->getType()->matches($currentModelClass->getName())) {
                    $inverseProperty = $possibleInverseProperty;
                    break;
                }
            }
        }

        if ($inverseProperty === null) {
            // TODO exception
        }

        $this->relationModelClass = $property->getType()->asClass();

        $localTable = TableName::for($property->getClass(), $alias);
        $this->localField = new FieldName($localTable, 'id');

        $joinTable = TableName::for($property->getType()->asClass(), "{$alias}.{$property->getName()}");
        $this->joinField = new FieldName($joinTable, $inverseProperty->getName() . '_id');
    }

    public
    function getStatement(): string
    {
        return sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->joinField->tableName,
            $this->localField,
            $this->joinField,
        );
    }

    public
    function getRelationName(): string
    {
        return $this->joinField->tableName->as;
    }

    public
    function getFieldNames(): array
    {
        return FieldName::make($this->relationModelClass, $this->joinField->tableName);
    }
}
