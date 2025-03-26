<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Exceptions\InvalidRelation;
use Tempest\Database\HasMany;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;

final readonly class HasManyRelation implements Relation
{
    private function __construct(
        private ClassReflector $relationModelClass,
        private FieldDefinition $localField,
        private FieldDefinition $joinField,
    ) {}

    public static function fromInference(PropertyReflector $property, string $alias): self
    {
        $relationModelClass = self::getRelationModelClass($property);

        $inverseProperty = null;

        foreach ($relationModelClass->getPublicProperties() as $potentialInverseProperty) {
            if ($potentialInverseProperty->getType()->equals($property->getClass()->getType())) {
                $inverseProperty = $potentialInverseProperty;

                break;
            }
        }

        if ($inverseProperty === null) {
            throw InvalidRelation::inversePropertyNotFound(
                $property->getClass()->getName(),
                $property->getName(),
                $relationModelClass->getName(),
            );
        }

        $localTable = TableDefinition::for($property->getClass(), $alias);
        $localField = new FieldDefinition($localTable, 'id');

        $joinTable = TableDefinition::for($relationModelClass, "{$alias}.{$property->getName()}[]");
        $joinField = new FieldDefinition($joinTable, $inverseProperty->getName() . '_id');

        return new self($relationModelClass, $localField, $joinField);
    }

    public static function getRelationModelClass(
        PropertyReflector $property,
        ?HasMany $relation = null,
    ): ClassReflector {
        if ($relation !== null && $relation->inverseClassName !== null) {
            return new ClassReflector($relation->inverseClassName);
        }

        return $property->getIterableType()->asClass();
    }

    public static function fromAttribute(HasMany $relation, PropertyReflector $property, string $alias): self
    {
        $relationModelClass = self::getRelationModelClass($property, $relation);

        $localTable = TableDefinition::for($property->getClass(), $alias);
        $localField = new FieldDefinition($localTable, $relation->localPropertyName);

        $joinTable = TableDefinition::for($relationModelClass, "{$alias}.{$property->getName()}[]");
        $joinField = new FieldDefinition($joinTable, $relation->inversePropertyName);

        return new self($relationModelClass, $localField, $joinField);
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
        return FieldDefinition::make($this->relationModelClass, $this->joinField->tableDefinition);
    }
}
