<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use BackedEnum;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use Tempest\Database\Builder\Relations\BelongsToRelation;
use Tempest\Database\Builder\Relations\HasManyRelation;
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
        $relations = [];
        $class = reflect($this->modelClass);
        $relationNames = explode('.', $relationName);
        $alias = TableName::for($class)->tableName;

        foreach ($relationNames as $relationNamePart) {
            $property = $class->getProperty($relationNamePart);

            if ($property->getType()->isIterable()) {
                $relations[] = new HasManyRelation($property, $alias);
                $class = $property->getIterableType()->asClass();
            } else {
                $relations[] = new BelongsToRelation($property, $alias);
                $class = $property->getType()->asClass();
            }

            $alias .= ".{$property->getName()}";
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
        return FieldName::make(reflect($this->modelClass));
    }
}
