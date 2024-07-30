<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use BackedEnum;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use Tempest\Database\Builder\Relations\BelongsToRelation;
use function Tempest\attribute;
use Tempest\Database\Eager;
use Tempest\Database\Exceptions\InvalidRelation;
use Tempest\Mapper\CastWith;
use function Tempest\reflect;
use Tempest\Support\Reflection\ClassReflector;
use function Tempest\type;

/** @phpstan-ignore-next-line */
readonly class ModelDefinition
{
    public function __construct(
        /** @var class-string<\Tempest\Database\Model> $modelClass */
        protected string $modelClass,
    ) {
    }

    /** @return \Tempest\Database\Builder\Relations\Relation[] */
    public function getRelations(string $relationName): array
    {
        return BelongsToRelation::make(reflect($this->modelClass), $relationName);

        $parentDefinition = $this;

        foreach (explode('.', $relationName) as $relationPart) {
            try {
                // $class = Author
                // $property = books
                // book.author_id
                $property = $class->getProperty($relationPart);

                if ($property->isIterable()) {
                    $iterableType = $property->getIterableType();

                    $type = RelationType::HAS_MANY;
                    $typeToMatch = $class->getType();

                    $class = $iterableType->asClass();

                    $matchingProperty = null;
                    
                    foreach ($class->getPublicProperties() as $property) {
                        if ($property->getType()->equals($typeToMatch)) {
                            $matchingProperty = $property;
                            break;
                        }
                    }
                    
                    $inverseDefinition = new InverseRelationDefinition(
                        modelClass: $class->getName(),
                        fieldName: $matchingProperty->getName() . '_id',
                    );
                } else {
                    $class = $class->getProperty($relationPart)->getType()->asClass();
                    $type = RelationType::BELONGS_TO;
                }

                $relation = new RelationDefinition(
                    modelClass: $class->getName(),
                    relationName: $relationPart,
                    parentDefinition: $parentDefinition,
                    inverseDefinition: $inverseDefinition ?? null,
                    type: $type,
                );

                $relations[] = $relation;

                $parentDefinition = $relation;
            } catch (ReflectionException $e) {
                throw new InvalidRelation(
                    modelClass: $this->modelClass,
                    relationName: $relationName,
                    relationPart: $relationPart,
                    previous: $e,
                );
            }
        }

        return $relations;
    }

    /** @return RelationDefinition[] */
    public function getEagerRelations(): array
    {
        $relations = [];

        foreach ($this->buildEagerRelationNames(reflect($this->modelClass)) as $relationName) {
            foreach ($this->getRelations($relationName) as $relation) {
                $relations[$relation->getRelationName()] = $relation;
            }
        }

        return $relations;
    }

    private function buildEagerRelationNames(ClassReflector $class): array
    {
        $relations = [];

        foreach ($class->getPublicProperties() as $property) {
            if (! $property->hasAttribute(Eager::class)) {
                continue;
            }

            $relations[] = $property->getName();

            foreach ($this->buildEagerRelationNames($property->getType()->asClass()) as $childRelation) {
                $relations[] = "{$property->getName()}.{$childRelation}";
            }
        }

        return $relations;
    }

    public function getTableName(): TableName
    {
        return ($this->modelClass)::table();
    }

    public function getFieldName(string $fieldName): FieldName
    {
        return new FieldName(
            tableName: $this->getTableName(),
            fieldName: $fieldName,
        );
    }

    /** @return \Tempest\Database\Builder\FieldName[] */
    public function getFieldNames(): array
    {
        $fieldNames = [];

        foreach ((new ReflectionClass($this->modelClass))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (is_a(type($property), BackedEnum::class, true)) {
                $fieldNames[] = $this->getFieldName($property->getName());

                continue;
            }

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            if (! $type->isBuiltin()) {
                $castWith = attribute(CastWith::class)
                    ->in($property)
                    ->first();

                if (! $castWith) {
                    $castWith = attribute(CastWith::class)
                        ->in($type->getName())
                        ->first();
                }

                if ($castWith) {
                    $fieldNames[] = $this->getFieldName($property->getName());
                }

                continue;
            }

            if ($type->getName() === 'array') {
                continue;
            }

            $fieldNames[] = $this->getFieldName($property->getName());
        }

        return $fieldNames;
    }
}
