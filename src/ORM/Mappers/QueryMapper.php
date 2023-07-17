<?php

declare(strict_types=1);

namespace Tempest\ORM\Mappers;

use ReflectionClass;
use ReflectionProperty;
use Tempest\Database\Query;
use Tempest\Interfaces\Mapper;
use Tempest\Interfaces\Model;

final readonly class QueryMapper implements Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool
    {
        return $objectOrClass === Query::class && $data instanceof Model;
    }

    public function map(object|string $objectOrClass, mixed $data): array|object
    {
        /** @var \Tempest\Interfaces\Model $model */
        $model = $data;

        $fields = $this->fields($model);

        if (! isset($fields['id'])) {
            return $this->createQuery($model, $fields);
        } else {
            return $this->updateQuery($model, $fields);
        }
    }

    private function createQuery(Model $model, array $fields): Query
    {
        $columns = implode(', ', array_keys($fields));

        $valuePlaceholders = implode(', ', array_map(
            fn (string $key) => ":{$key}",
            array_keys($fields),
        ));

        $table = $model::table();

        return new Query(
            "INSERT INTO {$table} ({$columns}) VALUES ({$valuePlaceholders});",
            $fields,
        );
    }

    private function updateQuery(Model $model, array $fields): Query
    {
        unset($fields['id']);

        $values = implode(', ', array_map(
            fn (string $key) => "{$key} = :{$key}",
            array_keys($fields),
        ));

        $table = $model::table();

        return new Query(
            "UPDATE {$table} SET {$values} WHERE id = {$model->id};",
            $fields,
        );
    }

    private function fields(Model $model): array
    {
        $class = new ReflectionClass($model);

        $fields = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (! $property->isInitialized($model)) {
                continue;
            }

            $value = $property->getValue($model);

            if ($value instanceof Model) {
                $value = $this->fields($value);
            }

            $fields[$property->getName()] = $value;
        }

        return $fields;
    }
}
