<?php

declare(strict_types=1);

namespace Tempest\Database\Mappers;

use ReflectionClass;
use ReflectionProperty;
use Tempest\Database\Model;
use Tempest\Database\Query;
use function Tempest\map;
use Tempest\Mapper\Mapper;
use function Tempest\type;

final readonly class ModelToQueryMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === Query::class && $from instanceof Model;
    }

    public function map(mixed $from, mixed $to): array|object
    {
        /** @var Model $model */
        $model = $from;

        $fields = $this->fields($model);

        if ($fields['id'] === null) {
            return $this->createQuery($model, $fields);
        } else {
            return $this->updateQuery($model, $fields);
        }
    }

    private function createQuery(Model $model, array $fields): Query
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
            $bindings[$key] = map($relation)->to(Query::class);
        }

        $valuePlaceholders = implode(', ', $valuePlaceholders);
        $columns = implode(', ', $columns);
        $table = $model::table();

        return new Query(
            "INSERT INTO {$table} ({$columns}) VALUES ({$valuePlaceholders});",
            $bindings,
        );
    }

    private function updateQuery(Model $model, array $fields): Query
    {
        $fields['id'] = $model->id;

        $values = implode(', ', array_map(
            fn (string $key) => "{$key} = :{$key}",
            array_filter(array_keys($fields), fn ($key) => $key !== 'id'),
        ));

        $table = $model::table();

        return new Query(
            "UPDATE {$table} SET {$values} WHERE id = :id;",
            $fields,
        );
    }

    private function relations(Model $model): array
    {
        $class = new ReflectionClass($model);

        $fields = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isInitialized($model)) {
                continue;
            }

            $value = $property->getValue($model);

            if (!$value instanceof Model) {
                continue;
            }

            // Only 1:1 or n:1 relations
            $fields[$property->getName()] = $value;
        }

        return $fields;
    }

    private function fields(Model $model): array
    {
        $class = new ReflectionClass($model);

        $fields = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isInitialized($model)) {
                continue;
            }

            $type = type($property);

            // 1:1 or n:1 relations
            if (is_a($type, Model::class, true)) {
                continue;
            }

            // 1:n relations
            if ($type === 'array') {
                continue;
            }

            $fields[$property->getName()] = $property->getValue($model);
        }

        return $fields;
    }
}
