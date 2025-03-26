<?php

declare(strict_types=1);

namespace Tempest\Database\Mappers;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\DatabaseModel;
use Tempest\Database\Query;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;

use function Tempest\map;

final readonly class ModelToQueryMapper implements Mapper
{
    public function __construct(
        private SerializerFactory $serializerFactory,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === Query::class && is_object($from);
    }

    // TODO: refactor to ModelQueryBuilder
    public function map(mixed $from, mixed $to): Query
    {
        $model = $from;

        $fields = $this->fields($model);

        if ($fields['id'] === null) {
            return $this->createQuery($model, $fields);
        }

        return $this->updateQuery($model, $fields);
    }

    private function createQuery(object $model, array $fields): Query
    {
        unset($fields['id']);

        $columns = [];
        $valuePlaceholders = [];
        $bindings = [];

        foreach ($fields as $key => $value) {
            $columns[] = $key;
            $valuePlaceholders[] = ":{$key}";
            $bindings[$key] = $value;
        }

        $relations = $this->relations($model);

        foreach ($relations as $key => $relation) {
            $key = "{$key}_id";
            $columns[] = $key;
            $valuePlaceholders[] = ":{$key}";
            $bindings[$key] = $relation !== null ? map($relation)->to(Query::class) : null;
        }

        $valuePlaceholders = implode(', ', $valuePlaceholders);
        $columns = implode(', ', $columns);
        $table = new ModelDefinition($model)->getTableDefinition();

        return new Query(
            "INSERT INTO {$table} ({$columns}) VALUES ({$valuePlaceholders});",
            $bindings,
        );
    }

    private function updateQuery(object $model, array $fields): Query
    {
        unset($fields['id']);

        $values = implode(', ', array_map(
            fn (string $key) => "{$key} = :{$key}",
            array_keys($fields),
        ));

        $fields['id'] = $model->id;

        $table = new ModelDefinition($model)->getTableDefinition();

        return new Query(
            "UPDATE {$table} SET {$values} WHERE id = :id;",
            $fields,
        );
    }

    private function relations(object $model): array
    {
        $class = new ClassReflector($model);

        $fields = [];

        foreach ($class->getPublicProperties() as $property) {
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

    private function fields(object $model): array
    {
        $class = new ClassReflector($model);

        $fields = [];

        foreach ($class->getPublicProperties() as $property) {
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
}
