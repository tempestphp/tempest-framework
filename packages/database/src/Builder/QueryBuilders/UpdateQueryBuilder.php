<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Exceptions\HasManyRelationCouldNotBeUpdated;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeUpdated;
use Tempest\Database\Id;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Database\QueryStatements\UpdateStatement;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Arr\MutableArray;
use Tempest\Support\Conditions\HasConditions;

use function Tempest\Database\model;
use function Tempest\Support\arr;

/**
 * @template TModelClass of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModelClass>
 * @uses \Tempest\Database\Builder\QueryBuilders\IsQueryBuilderWithWhere<TModelClass>
 */
final class UpdateQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, IsQueryBuilderWithWhere;

    private UpdateStatement $update;

    private array $bindings = [];

    public function __construct(
        /** @var class-string<TModelClass> $model */
        private readonly string|object $model,
        private readonly array|ImmutableArray $values,
        private readonly SerializerFactory $serializerFactory,
    ) {
        $this->update = new UpdateStatement(
            table: model($this->model)->getTableDefinition(),
        );
    }

    public function execute(mixed ...$bindings): ?Id
    {
        return $this->build()->execute(...$bindings);
    }

    /** @return self<TModelClass> */
    public function allowAll(): self
    {
        $this->update->allowAll = true;

        return $this;
    }

    /** @return self<TModelClass> */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    public function toSql(): string
    {
        return $this->build()->toSql();
    }

    public function build(mixed ...$bindings): Query
    {
        $values = $this->resolveValues();

        unset($values['id']);

        $this->update->values = $values;

        if (model($this->model)->isObjectModel() && is_object($this->model)) {
            $this->where('id = ?', id: $this->model->id->id);
        }

        foreach ($values as $value) {
            $bindings[] = $value;
        }

        foreach ($this->bindings as $binding) {
            $bindings[] = $binding;
        }

        return new Query($this->update, $bindings)->onDatabase($this->onDatabase);
    }

    private function resolveValues(): ImmutableArray
    {
        $modelDefinition = model($this->model);

        if (! $modelDefinition->isObjectModel()) {
            return arr($this->values);
        }

        $values = arr();

        $modelClass = new ClassReflector($this->model);

        foreach ($this->values as $column => $value) {
            $property = $modelClass->getProperty($column);

            if ($modelDefinition->getHasMany($property->getName())) {
                throw new HasManyRelationCouldNotBeUpdated($modelClass->getName(), $property->getName());
            }

            if ($modelDefinition->getHasOne($property->getName())) {
                throw new HasOneRelationCouldNotBeUpdated($modelClass->getName(), $property->getName());
            }

            if ($modelDefinition->isRelation($property)) {
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

            if (! $property->getType()->isRelation() && ! $property->getIterableType()?->isRelation()) {
                $serializer = $this->serializerFactory->forProperty($property);

                if ($value !== null && $serializer !== null) {
                    $value = $serializer->serialize($value);
                }
            }

            $values[$column] = $value;
        }

        return $values;
    }

    private function getStatementForWhere(): HasWhereStatements
    {
        return $this->update;
    }
}
