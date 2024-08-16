<?php

declare(strict_types=1);

namespace Tempest\Database\Mappers;

use BackedEnum;
use Tempest\Database\DatabaseModel;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tempest\Mapper\Mapper;
use Tempest\Support\Reflection\ClassReflector;
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
            $idField = $table->tableName . '.id';

            $id = $row[$idField];

            $model = $models[$id] ?? $class->newInstanceWithoutConstructor();

            $models[$id] = $this->parse($class, $model, $row);
        }

        return $this->makeLazyCollection($models);
    }

    private function parse(ClassReflector $class, DatabaseModel $model, array $row): DatabaseModel
    {
        foreach ($row as $key => $value) {
            $keyParts = explode('.', $key);

            $propertyName = $keyParts[1];

            $count = count($keyParts);

            if ($count > 3) {
                $property = $class->getProperty($propertyName);

                $childModel = $property->get($model, $property->getType()->asClass()->newInstanceWithoutConstructor());

                unset($keyParts[0]);

                $property->set($model, $this->parse(
                    $class->getProperty($propertyName)->getType()->asClass(),
                    $childModel,
                    [implode('.', $keyParts) => $value]
                ));
            } elseif ($count === 3) {
                if (str_contains($keyParts[1], '[]')) {
                    $property = $class->getProperty(rtrim($propertyName, '[]'));

                    $childId = $row[$keyParts[0] . '.' . $keyParts[1] . '.id'];

                    $model = $this->parseHasMany(
                        $property,
                        $model,
                        (string)$childId,
                        $keyParts[2],
                        $value,
                    );
                } else {
                    $property = $class->getProperty($propertyName);

                    $model = $this->parseBelongsTo(
                        $property,
                        $model,
                        $keyParts[2],
                        $value,
                    );
                }
            } else {
                $property = $class->getProperty($propertyName);

                $model = $this->parseProperty($property, $model, $value);
            }
        }

        return $model;
    }

    private function parseProperty(PropertyReflector $property, DatabaseModel $model, mixed $value): DatabaseModel
    {
        $type = $property->getType();

        $value = match (true) {
            $type->matches(BackedEnum::class) => $value ? $type->asClass()->callStatic('tryFrom', $value) : null,
            $type->matches(Id::class) => new Id($value),
            default => $value,
        };

        $property->set($model, $value);

        return $model;
    }

    private function parseBelongsTo(PropertyReflector $property, DatabaseModel $model, string $childProperty, mixed $value): DatabaseModel
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

    private function parseHasMany(PropertyReflector $property, DatabaseModel $model, string $childId, string $childProperty, mixed $value): DatabaseModel
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

    private function makeLazyModel(DatabaseModel $model): DatabaseModel
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
