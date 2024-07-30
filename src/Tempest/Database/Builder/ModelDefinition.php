<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use BackedEnum;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use function Tempest\attribute;
use Tempest\Database\Exceptions\InvalidRelation;
use Tempest\Mapper\CastWith;
use function Tempest\type;

/** @phpstan-ignore-next-line  */
readonly class ModelDefinition
{
    public function __construct(
        /** @var class-string<\Tempest\Database\Model> $modelClass */
        protected string $modelClass,
    ) {
    }

    /** @return RelationDefinition[] */
    public function getRelations(string $relationName): array
    {
        $relations = [];

        $class = new ReflectionClass($this->modelClass);
        $parentDefinition = $this;

        foreach (explode('.', $relationName) as $relationPart) {
            try {
                $class = new ReflectionClass(type($class->getProperty($relationPart)));

                $relation = new RelationDefinition(
                    $class->getName(),
                    $relationPart,
                    $parentDefinition,
                );

                $relations[] = $relation;

                $parentDefinition = $relation;
            } catch (ReflectionException) {
                throw new InvalidRelation($this->modelClass, $relationName, $relationPart);
            }
        }

        return $relations;
    }

    //    /** @return RelationDefinition[] */
    //    public function getEagerRelations(): array
    //    {
    //        $relations = [];
    //
    //        $class = reflect($this->modelClass);
    //
    //        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
    //        }
    //
    //        dd($relations);
    //
    //        return $relations;
    //    }

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
