<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeUpdated;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeUpdated;
use Tempest\Database\Id;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Database\QueryStatements\UpdateStatement;
use Tempest\Mapper\SerializerFactory;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;

use function Tempest\Database\model;
use function Tempest\Support\arr;

/**
 * @template TModelClass of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModelClass>
 * @uses \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModelClass>
 */
final class UpdateQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods, TapsQueryBuilder;

    private UpdateStatement $update;

    private array $bindings = [];

    private ModelInspector $model;

    public function __construct(
        /** @var class-string<TModelClass>|string|TModelClass $model */
        string|object $model,
        private readonly array|ImmutableArray $values,
        private readonly SerializerFactory $serializerFactory,
    ) {
        $this->model = model($model);

        $this->update = new UpdateStatement(
            table: $this->model->getTableDefinition(),
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

        if ($this->model->isObjectModel() && is_object($this->model->instance)) {
            $this->whereField(
                $this->model->getPrimaryKey(),
                $this->model->getPrimaryKeyValue()->id,
            );
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
        if (! $this->model->isObjectModel()) {
            return arr($this->values);
        }

        $values = arr();

        foreach ($this->values as $column => $value) {
            $property = $this->model->reflector->getProperty($column);

            if ($this->model->getHasMany($property->getName())) {
                throw new HasManyRelationCouldNotBeUpdated($this->model->getName(), $property->getName());
            }

            if ($this->model->getHasOne($property->getName())) {
                throw new HasOneRelationCouldNotBeUpdated($this->model->getName(), $property->getName());
            }

            if ($this->model->isRelation($property)) {
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

    private function getModel(): ModelInspector
    {
        return $this->model;
    }

    private function clone(): self
    {
        return clone $this;
    }
}
