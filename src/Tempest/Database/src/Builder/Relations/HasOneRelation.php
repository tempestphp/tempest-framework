<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Exceptions\InvalidRelation;
use Tempest\Database\HasOne;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;

final readonly class HasOneRelation implements Relation
{
    private ClassReflector $relationModelClass;

    private FieldDefinition $localField;

    private FieldDefinition $joinField;

    public function __construct(PropertyReflector $property, string $alias)
    {
        $hasOneAttribute = $property->getAttribute(HasOne::class);
        $inversePropertyName = $hasOneAttribute?->inversePropertyName;

        $inverseProperty = $inversePropertyName === null
            ? $this->findInversePropertyByType($property)
            : $this->findInversePropertyByName($property, $inversePropertyName);

        $this->relationModelClass = $property->getType()->asClass();

        $localTable = TableDefinition::for($property->getClass(), $alias);
        $this->localField = new FieldDefinition($localTable, 'id');

        $joinTable = TableDefinition::for($property->getType()->asClass(), "{$alias}.{$property->getName()}");
        $this->joinField = new FieldDefinition($joinTable, $inverseProperty->getName() . '_id');
    }

    public function getStatement(): string
    {
        return sprintf(
            'LEFT JOIN %s ON %s = %s',
            $this->joinField->tableDefinition,
            $this->localField,
            $this->joinField,
        );
    }

    public function getRelationName(): string
    {
        return $this->joinField->tableDefinition->as;
    }

    public function getFieldDefinitions(): ImmutableArray
    {
        return FieldDefinition::all($this->relationModelClass, $this->joinField->tableDefinition);
    }

    private function findInversePropertyByType(PropertyReflector $property): PropertyReflector
    {
        $currentModelClass = $property->getClass();
        $propertyClass = $property->getType()->asClass();

        foreach ($propertyClass->getPublicProperties() as $possibleInverseProperty) {
            if ($possibleInverseProperty->getType()->matches($currentModelClass->getName())) {
                return $possibleInverseProperty;
            }
        }

        throw InvalidRelation::inversePropertyNotFound(
            $currentModelClass->getName(),
            $property->getName(),
            $propertyClass->getName(),
        );
    }

    private function findInversePropertyByName(PropertyReflector $property, string $inversePropertyName): PropertyReflector
    {
        $currentModelClass = $property->getClass();
        $relatedClass = $property->getType()->asClass();

        if (! $relatedClass->hasProperty($inversePropertyName)) {
            throw InvalidRelation::inversePropertyMissing(
                $currentModelClass->getName(),
                $property->getName(),
                $relatedClass->getName(),
                $inversePropertyName,
            );
        }

        $inverseProperty = $relatedClass->getProperty($inversePropertyName);
        $expectedType = $currentModelClass->getType();

        if (! $inverseProperty->getType()->matches($expectedType->getName())) {
            throw InvalidRelation::inversePropertyInvalidType(
                $currentModelClass->getName(),
                $property->getName(),
                $relatedClass->getName(),
                $inversePropertyName,
                $property->getType()->getName(),
                $inverseProperty->getType()->getName(),
            );
        }

        return $inverseProperty;
    }
}
