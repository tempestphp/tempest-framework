<?php

declare(strict_types=1);

namespace Tempest\Database\Mappers;

use BackedEnum;
use Tempest\Database\Id;
use Tempest\Database\Model;
use Tempest\Database\Query;
use Tempest\Support\Reflection\ClassReflector;
use Tempest\Mapper\Mapper;
use Tempest\Support\Reflection\PropertyReflector;

final readonly class QueryToModelMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $from instanceof Query;
    }

    public function map(mixed $from, mixed $to): array
    {
        $class = new ClassReflector($to);
        $table = $class->callStatic('table');

        $models = [];

        foreach ($from->fetch() as $row) {
            lw($row);
            $idField = $table->tableName . '.id';

            $id = $row[$idField];

            $model = $models[$id] ?? $class->newInstanceWithoutConstructor();

            $models[$id] = $this->parse($class, $model, $row);
        }

        return $this->makeLazyCollection($models);
    }

    private function parse(ClassReflector $class, Model $model, array $row): Model
    {
        foreach ($row as $key => $value) {
            $keyParts = explode('.', $key);

            $propertyName = $keyParts[1];

            if (str_ends_with($propertyName, '[]')) {
                $property = $class->getProperty(rtrim($propertyName, '[]'));

                $childId = $row[$keyParts[0] . '.' . $keyParts[1] . '.id'];

                $model = $this->parseHasMany(
                    $property,
                    $model,
                    (string)$childId,
                    $keyParts[2],
                    $value,
                );
            } elseif (count($keyParts) > 2) {
                $property = $class->getProperty(rtrim($propertyName));

                $model = $this->parseBelongsTo(
                    $property,
                    $model,
                    $keyParts[2],
                    $value,
                );
            } else {
                $property = $class->getProperty($propertyName);

                $model = $this->parseProperty($property, $model, $value);
            }
        }

        return $model;
    }

    private function parseProperty(PropertyReflector $property, Model $model, mixed $value): Model
    {
        $type = $property->getType();

        $value = match (true) {
            $type->matches(BackedEnum::class) => $type->asClass()->callStatic('tryFrom', $value),
            $type->matches(Id::class) => new Id($value),
            default => $value,
        };

        $property->set($model, $value);

        return $model;
    }

    private function parseBelongsTo(PropertyReflector $property, Model $model, string $childProperty, mixed $value): Model
    {
        $childModel = $property->get(
            $model,
            $property->getType()->asClass()->newInstanceWithoutConstructor(),
        );

        $childProperty = (new ClassReflector($childModel))->getProperty($childProperty);

        // TODO: must pass through the mapper
        $this->parseProperty(
            $childProperty,
            $childModel,
            $value,
        );

        $property->set($model, $childModel);

        return $model;
    }

    private function parseHasMany(PropertyReflector $property, Model $model, string $childId, string $childProperty, mixed $value): Model
    {
        $collection = $property->get($model, []);

        $childModel = $collection[$childId] ?? $property->getIterableType()->asClass()->newInstanceWithoutConstructor();

        $childProperty = (new ClassReflector($childModel))->getProperty($childProperty);

        // TODO: must pass through the mapper
        $this->parseProperty(
            $childProperty,
            $childModel,
            $value,
        );

        $collection[$childId] = $childModel;

        $property->set($model, $collection);

        return $model;
    }

    private function makeLazyCollection(array $models): array
    {
        $lazy = [];

        foreach ($models as $model) {
            $lazy[] = $this->makeLazyModel($model);
        }

        return $lazy;
    }

    private function makeLazyModel(Model $model): Model
    {
        $classReflector = new ClassReflector($model);

        foreach ($classReflector->getPublicProperties() as $property) {
            if ($property->isIterable()) {
                foreach ($property->get($model) as $childModel) {
                    $this->makeLazyModel($childModel);
                }

                break;
            }

            if ($property->isUninitialized($model)) {
                $property->unset($model);
            }
        }

        return $model;
    }
}
