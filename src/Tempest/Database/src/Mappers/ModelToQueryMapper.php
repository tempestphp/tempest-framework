<?php

declare(strict_types=1);

namespace Tempest\Database\Mappers;

use Tempest\Database\DatabaseModel;
use Tempest\Database\Query;
use Tempest\Mapper\Casters\CasterFactory;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\Serializers\SerializerFactory;
use Tempest\Reflection\ClassReflector;

use function Tempest\map;

final readonly class ModelToQueryMapper implements Mapper
{
    public function __construct(
        private SerializerFactory $serializerFactory,
    ) {
    }

    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === Query::class && $from instanceof DatabaseModel;
    }

    // TODO: refactor to ModelQueryBuilder
    public function map(mixed $from, mixed $to): Query
    {
        /** @var DatabaseModel $model */
        $model = $from;

        $fields = $this->fields($model);

        if ($fields['id'] === null) {
            return $this->createQuery($model, $fields);
        }

        return $this->updateQuery($model, $fields);
    }

    private function createQuery(DatabaseModel $model, array $fields): Query
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

    private function updateQuery(DatabaseModel $model, array $fields): Query
    {
        unset($fields['id']);

        $values = implode(', ', array_map(
            fn (string $key) => "{$key} = :{$key}",
            array_keys($fields),
        ));

        $fields['id'] = $model->getId();

        $table = $model::table();

        return new Query(
            "UPDATE {$table} SET {$values} WHERE id = :id;",
            $fields,
        );
    }

    private function relations(DatabaseModel $model): array
    {
        $class = new ClassReflector($model);

        $fields = [];

        foreach ($class->getPublicProperties() as $property) {
            if (! $property->isInitialized($model)) {
                continue;
            }

            $value = $property->getValue($model);

            if (! ($value instanceof DatabaseModel)) {
                continue;
            }

            // Only 1:1 or n:1 relations
            $fields[$property->getName()] = $value;
        }

        return $fields;
    }

    private function fields(DatabaseModel $model): array
    {
        $class = new ClassReflector($model);

        $fields = [];

        foreach ($class->getPublicProperties() as $property) {
            if (! $property->isInitialized($model)) {
                continue;
            }

            // 1:1 or n:1 relations
            if ($property->getType()->matches(DatabaseModel::class)) {
                continue;
            }

            // 1:n relations
            if ($property->getIterableType()?->matches(DatabaseModel::class)) {
                continue;
            }

            $value = $property->getValue($model);

            // Check if caster is available for value serialization
            if ($value !== null && ($serializer = $this->serializerFactory->forProperty($property))) {
                $value = $serializer->serialize($value);
            }

            $fields[$property->getName()] = $value;
        }

        return $fields;
    }
}
