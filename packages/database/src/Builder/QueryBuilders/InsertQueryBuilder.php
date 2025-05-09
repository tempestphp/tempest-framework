<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\InsertStatement;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;

final class InsertQueryBuilder implements BuildsQuery
{
    private InsertStatement $insert;

    private array $after = [];

    public function __construct(
        private readonly string|object $model,
        private readonly array $rows,
        private readonly SerializerFactory $serializerFactory,
    ) {
        $this->insert = new InsertStatement($this->resolveTableDefinition());
    }

    public function execute(mixed ...$bindings): Id
    {
        $id = $this->build()->execute(...$bindings);

        foreach ($this->after as $after) {
            $query = $after($id);

            if ($query instanceof BuildsQuery) {
                $query->build()->execute();
            }
        }

        return $id;
    }

    public function build(mixed ...$bindings): Query
    {
        foreach ($this->resolveEntries() as $entry) {
            $this->insert->addEntry($entry);

            foreach ($entry as $value) {
                $bindings[] = $value;
            }
        }

        return new Query(
            $this->insert,
            $bindings,
        );
    }

    public function then(Closure ...$callbacks): self
    {
        $this->after = [...$this->after, ...$callbacks];

        return $this;
    }

    private function resolveEntries(): array
    {
        $entries = [];

        foreach ($this->rows as $model) {
            // Raw entries are straight up added
            if (is_array($model) || $model instanceof ImmutableArray) {
                $entries[] = $model;

                continue;
            }

            // The rest are model objects
            $modelClass = new ClassReflector($model);

            $entry = [];

            // Including all public properties
            foreach ($modelClass->getPublicProperties() as $property) {
                if (! $property->isInitialized($model)) {
                    continue;
                }

                // HasMany relations are skipped
                if ($property->getIterableType()?->isRelation()) {
                    continue;
                }

                $column = $property->getName();

                $value = $property->getValue($model);

                // BelongsTo and reverse HasMany relations are included
                if ($property->getType()->isRelation()) {
                    $column .= '_id';

                    $value = match (true) {
                        $value === null => null,
                        isset($value->id) => $value->id->id,
                        default => new InsertQueryBuilder(
                            $value::class,
                            [$value],
                            $this->serializerFactory,
                        )->build(),
                    };
                }

                // Check if value needs serialization
                $serializer = $this->serializerFactory->forProperty($property);

                if ($value !== null && $serializer !== null) {
                    $value = $serializer->serialize($value);
                }

                $entry[$column] = $value;
            }

            $entries[] = $entry;
        }

        return $entries;
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
