<?php

namespace Tempest\Database\Builder\Queries;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Query;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;

/**
 * @template TModelClass of object
 */
final readonly class CreateModelQuery
{
    public function __construct(
        private SerializerFactory $serializerFactory,
    ) {
    }

    public function build(object $model): Query
    {
        $modelClass = new ClassReflector($model);
        $modelDefinition = new ModelDefinition($model);

        $fields = $this->fields($modelClass, $model);

        unset($fields['id']);

        $columns = [];
        $valuePlaceholders = [];
        $bindings = [];

        foreach ($fields as $key => $value) {
            $columns[] = $key;
            $valuePlaceholders[] = ":{$key}";
            $bindings[$key] = $value;
        }

        $relations = $this->relations($modelClass, $model);

        foreach ($relations as $key => $relation) {
            $key = "{$key}_id";
            $columns[] = $key;
            $valuePlaceholders[] = ":{$key}";

            if ($relation !== null) {
                $bindings[$key] = $relation->id ?? $this->build($relation);
            } else {
                $bindings[$key] = null;
            }
        }

        $valuePlaceholders = implode(', ', $valuePlaceholders);
        $columns = implode(', ', $columns);
        $table = $modelDefinition->getTableDefinition();

        return new Query(
            "INSERT INTO {$table} ({$columns}) VALUES ({$valuePlaceholders});",
            $bindings,
        );
    }

    // TODO: move to model definition class?
    private function fields(ClassReflector $modelClass, object $model): array
    {
        $fields = [];

        foreach ($modelClass->getPublicProperties() as $property) {
            if (! $property->isInitialized($model)) {
                continue;
            }

            // 1:1 or n:1 relations
            if ($property->getType()->isRelation()) {
                continue;
            }

            // 1:n relations
            if ($property->getIterableType()?->isRelation()) {
                continue;
            }

            $value = $property->getValue($model);

            // Check if serializer is available for value serialization
            if ($value !== null && ($serializer = $this->serializerFactory->forProperty($property))) {
                $value = $serializer->serialize($value);
            }

            $fields[$property->getName()] = $value;
        }

        return $fields;
    }

    // TODO: move to model definition class?
    private function relations(ClassReflector $modelClass, object $model): array
    {
        $fields = [];

        foreach ($modelClass->getPublicProperties() as $property) {
            if (! $property->isInitialized($model)) {
                continue;
            }

            if (! $property->getType()->isRelation()) {
                continue;
            }

            $value = $property->getValue($model);

            // Only 1:1 or n:1 relations
            $fields[$property->getName()] = $value;
        }

        return $fields;
    }
}