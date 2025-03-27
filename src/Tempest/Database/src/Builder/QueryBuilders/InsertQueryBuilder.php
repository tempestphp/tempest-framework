<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;

use function Tempest\Support\arr;

final class InsertQueryBuilder
{
    public function __construct(
        private string|object $model,
        private array $rows,
        private SerializerFactory $serializerFactory,
    ) {}

    public function execute(...$bindings): Id
    {
        return $this->build()->execute(...$bindings);
    }

    public function build(): Query
    {
        $table = $this->resolveTableDefinition();

        $columns = $this->resolveColumns();

        $values = $this->resolveValues($columns);

        $valuesPlaceholders = arr($values)
            ->map(function (array $row) {
                return sprintf(
                    '(%s)',
                    arr($row)->map(fn (mixed $value) => '?')->implode(', '),
                );
            })
            ->implode(', ');

        return new Query(
            sprintf(
                <<<SQL
                INSERT INTO %s (%s)
                VALUES %s
                SQL,
                $table,
                arr($columns)->map(fn (string $column) => "`{$column}`")->implode(', '),
                $valuesPlaceholders,
            ),
            arr($values)->flatten(1)->toArray(),
        );
    }

    private function resolveColumns(): array
    {
        $firstEntry = $this->rows[array_key_first($this->rows)];

        if (is_array($firstEntry)) {
            return array_keys($firstEntry);
        }

        if (! is_object($firstEntry)) {
            // TODO: Shouldn't be allowed
        }

        $modelClass = new ClassReflector($firstEntry);

        $columns = [];

        foreach ($modelClass->getPublicProperties() as $property) {
            if (! $property->isInitialized($firstEntry)) {
                continue;
            }

            // 1:n relations
            if ($property->getIterableType()?->isRelation()) {
                continue;
            }

            if ($property->getType()->isRelation()) {
                $columns[] = $property->getName() . '_id';
            } else {
                $columns[] = $property->getName();
            }
        }

        return $columns;
    }

    private function resolveValues(array $columns): array
    {
        $values = [];

        foreach ($this->rows as $model) {
            if (is_array($model)) {
                $values[] = $model;

                continue;
            }

            if (! is_object($model)) {
                // TODO: this should now be allowed
            }

            $modelClass = new ClassReflector($model);

            $values[] = arr($columns)
                ->map(function (string $column) use ($modelClass, $model) {
                    // TODO: improve
                    $column = str($column)->replaceEnd('_id', '');

                    $property = $modelClass->getProperty($column);

                    $value = $model->{$column};

                    if ($value === null) {
                        return $value;
                    }

                    if ($property->getType()->isRelation()) {
                        if (isset($value->id)) {
                            $value = $value->id->id;
                        } else {
                            $value = new InsertQueryBuilder(
                                $value::class,
                                [$value],
                                $this->serializerFactory,
                            )->build();
                        }
                    }

                    // Check if serializer is available for value serialization
                    if (($serializer = $this->serializerFactory->forProperty($property)) !== null) {
                        return $serializer->serialize($value);
                    }

                    return $value;
                })
                ->toArray();
        }

        return $values;
    }

    private function resolveTableDefinition(): TableDefinition
    {
        $modelDefinition = ModelDefinition::tryFrom($this->model);

        if ($modelDefinition === null) {
            return new TableDefinition($this->model);
        }

        return $modelDefinition->getTableDefinition();
    }
}
