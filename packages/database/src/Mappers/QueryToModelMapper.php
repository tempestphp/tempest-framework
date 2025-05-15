<?php

declare(strict_types=1);

namespace Tempest\Database\Mappers;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Mapper\CasterFactory;
use Tempest\Mapper\Mapper;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;

// TODO: remove
final readonly class QueryToModelMapper implements Mapper
{
    public function __construct(
        private CasterFactory $casterFactory,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): array
    {
        /** @var \Tempest\Database\Query $from */
        /** @var class-string $to */
        $class = new ClassReflector($to);
        $table = new ModelDefinition($class)->getTableDefinition();

        $models = [];

        foreach ($from->fetch() as $row) {
            $idField = $table->name . '.id';

            $id = $row[$idField];

            $model = $models[$id] ?? $class->newInstanceWithoutConstructor();

            $models[$id] = $this->parse($class, $model, $row);
        }

        return $this->makeLazyCollection($models);
    }

    private function parse(ClassReflector $class, object $model, array $row): object
    {
        foreach ($row as $key => $value) {
            $keyParts = explode('.', $key);

            $propertyName = $keyParts[1];

            $count = count($keyParts);

            // TODO: clean up and document
            if ($count > 3) {
                $property = $class->getProperty(rtrim($propertyName, '[]'));

                if ($property->getIterableType()?->isRelation()) {
                    $collection = $property->get($model, []);
                    $childId = $row[$keyParts[0] . '.' . $keyParts[1] . '.id'];

                    if ($childId) {
                        $iterableType = $property->getIterableType();

                        $childModel = $collection[$childId] ?? $iterableType->asClass()->newInstanceWithoutConstructor();

                        unset($keyParts[0]);

                        $collection[$childId] = $this->parse(
                            $iterableType->asClass(),
                            $childModel,
                            [implode('.', $keyParts) => $value],
                        );
                    }

                    $property->set($model, $collection);
                } else {
                    $childModelType = $property->getType();

                    $childModel = $property->get($model, $childModelType->asClass()->newInstanceWithoutConstructor());

                    unset($keyParts[0]);

                    $property->set($model, $this->parse(
                        $childModelType->asClass(),
                        $childModel,
                        [implode('.', $keyParts) => $value],
                    ));
                }
            } elseif ($count === 3) {
                $childId = $row[$keyParts[0] . '.' . $keyParts[1] . '.id'] ?? null;

                if (str_contains($keyParts[1], '[]')) {
                    $property = $class->getProperty(rtrim($propertyName, '[]'));

                    $model = $this->parseHasMany(
                        $property,
                        $model,
                        (string) $childId,
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

    private function parseProperty(PropertyReflector $property, object $model, mixed $value): object
    {
        $caster = $this->casterFactory->forProperty($property);

        if ($value && $caster !== null) {
            $value = $caster->cast($value);
        }

        if ($value === null && ! $property->isNullable()) {
            return $model;
        }

        $property->set($model, $value);

        return $model;
    }

    private function parseBelongsTo(PropertyReflector $property, object $model, string $childProperty, mixed $value): object
    {
        $childModel = $property->get(
            $model,
            $property->getType()->asClass()->newInstanceWithoutConstructor(),
        );

        $childProperty = new ClassReflector($childModel)->getProperty($childProperty);

        // TODO: must pass through the mapper
        $this->parseProperty(
            $childProperty,
            $childModel,
            $value,
        );

        $property->set($model, $childModel);

        return $model;
    }

    private function parseHasMany(PropertyReflector $property, object $model, ?string $childId, string $childProperty, mixed $value): object
    {
        $collection = $property->get($model, []);

        if (! $childId) {
            $property->set($model, $collection);

            return $model;
        }

        $childModel = $collection[$childId] ?? $property->getIterableType()->asClass()->newInstanceWithoutConstructor();

        $childProperty = new ClassReflector($childModel)->getProperty($childProperty);

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

    private function makeLazyModel(object $model): object
    {
        $classReflector = new ClassReflector($model);

        foreach ($classReflector->getPublicProperties() as $property) {
            if ($property->isUninitialized($model)) {
                $property->unset($model);

                continue;
            }

            if ($property->getIterableType()?->isRelation()) {
                foreach ($property->get($model) as $childModel) {
                    $this->makeLazyModel($childModel);
                }

                break;
            }
        }

        return $model;
    }
}
