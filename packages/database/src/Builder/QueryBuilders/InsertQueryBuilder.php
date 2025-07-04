<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeInsterted;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeInserted;
use Tempest\Database\Id;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\InsertStatement;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tests\Tempest\Integration\ORM\Models\DateTimeModel;

use function Tempest\Database\model;
use function Tempest\map;

final class InsertQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase;

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

    public function toSql(): string
    {
        return $this->build()->toSql();
    }

    public function build(mixed ...$bindings): Query
    {
        $definition = model($this->model);

        foreach ($this->resolveData() as $data) {
            foreach ($data as $key => $value) {
                if ($definition->getHasMany($key)) {
                    throw new HasManyRelationCouldNotBeInsterted($definition->getName(), $key);
                }

                if ($definition->getHasOne($key)) {
                    throw new HasOneRelationCouldNotBeInserted($definition->getName(), $key);
                }

                $bindings[] = $value;
            }

            $this->insert->addEntry($data);
        }

        return new Query($this->insert, $bindings)->onDatabase($this->onDatabase);
    }

    public function then(Closure ...$callbacks): self
    {
        $this->after = [...$this->after, ...$callbacks];

        return $this;
    }

    private function resolveData(): array
    {
        $entries = [];

        $baseModelName = model($this->model)->getName();

        foreach ($this->rows as $model) {
            // Raw entries are converted to model objects
            if (is_array($model) || $model instanceof ImmutableArray) {
                $model = map($model)->with(ArrayToObjectMapper::class)->to($baseModelName);
            }

            // The rest are model objects
            $definition = model($model);

            $modelClass = new ClassReflector($model);

            $entry = [];

            // Including all public properties
            foreach ($modelClass->getPublicProperties() as $property) {
                if (! $property->isInitialized($model)) {
                    continue;
                }

                // HasMany and HasOne relations are skipped
                if ($definition->getHasMany($property->getName()) || $definition->getHasOne($property->getName())) {
                    continue;
                }

                $column = $property->getName();

                $value = $property->getValue($model);

                // BelongsTo and reverse HasMany relations are included
                if ($definition->isRelation($property)) {
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

                // Check if the value needs serialization
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
