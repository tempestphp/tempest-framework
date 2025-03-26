<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

use Tempest\Database\BelongsTo;
use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;

final readonly class BelongsToRelation implements Relation
{
    private function __construct(
        private ClassReflector $relationModelClass,
        private FieldDefinition $localField,
        private FieldDefinition $joinField,
    ) {}

    public static function fromInference(PropertyReflector $property, string $alias): self
    {
        $relationModelClass = $property->getType()->asClass();

        $localTable = TableDefinition::for($property->getClass(), $alias);
        $localField = new FieldDefinition($localTable, $property->getName() . '_id');

        $joinTable = TableDefinition::for($property->getType()->asClass(), "{$alias}.{$property->getName()}");
        $joinField = new FieldDefinition($joinTable, 'id');

        return new self($relationModelClass, $localField, $joinField);
    }

    public static function fromAttribute(BelongsTo $belongsTo, PropertyReflector $property, string $alias): self
    {
        $relationModelClass = $property->getType()->asClass();

        $localTable = TableDefinition::for($property->getClass(), $alias);
        $localField = new FieldDefinition($localTable, $belongsTo->localPropertyName);

        $joinTable = TableDefinition::for($property->getType()->asClass(), "{$alias}.{$property->getName()}");
        $joinField = new FieldDefinition($joinTable, $belongsTo->inversePropertyName);

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
