<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeUpdated;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeUpdated;
use Tempest\Database\OnDatabase;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Database\QueryStatements\UpdateStatement;
use Tempest\Mapper\SerializerFactory;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;
use function Tempest\Support\arr;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 * @uses \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModel>
 */
final class UpdateQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods;

    private UpdateStatement $update;

    private array $bindings = [];

    private ModelInspector $model;

    /**
     * @param class-string<TModel>|string|TModel $model
     */
    public function __construct(
        string|object $model,
        private readonly array|ImmutableArray $values,
        private readonly SerializerFactory $serializerFactory,
    ) {
        $this->model = inspect($model);

        $this->update = new UpdateStatement(
            table: $this->model->getTableDefinition(),
        );
    }

    /**
     * Executes the update query and returns the primary key of the updated record.
     */
    public function execute(mixed ...$bindings): ?PrimaryKey
    {
        return $this->build()->execute(...$bindings);
    }

    /**
     * Allows the update operation to proceed without WHERE conditions, updating all records.
     *
     * @return self<TModel>
     */
    public function allowAll(): self
    {
        $this->update->allowAll = true;

        return $this;
    }

    /**
     * Binds the provided values to the query, allowing for parameterized queries.
     *
     * @return self<TModel>
     */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    /**
     * Returns the SQL statement without the bindings.
     */
    public function toSql(): ImmutableString
    {
        return $this->build()->toSql();
    }

    /**
     * Returns the SQL statement with bindings. This method may generate syntax errors, it is not recommended to use it other than for debugging.
     */
    public function toRawSql(): ImmutableString
    {
        return $this->build()->toRawSql();
    }

    public function build(mixed ...$bindings): Query
    {
        $values = $this->resolveValues();

        if ($this->model->hasPrimaryKey()) {
            $primaryKey = $this->model->getPrimaryKey();
            unset($values[$primaryKey]);
        }

        $this->update->values = $values;

        if ($this->model->isObjectModel() && is_object($this->model->instance) && $this->model->hasPrimaryKey()) {
            $primaryKeyValue = $this->model->getPrimaryKeyValue();

            if ($primaryKeyValue !== null) {
                $this->where($this->model->getPrimaryKey(), $primaryKeyValue->value);
            }
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
                $relationModel = inspect($property->getType()->asClass());
                $primaryKey = $relationModel->getPrimaryKey() ?? 'id';
                $column .= '_' . $primaryKey;

                $value = match (true) {
                    $value === null => null,
                    isset($value->{$primaryKey}) => $value->{$primaryKey}->value,
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
}
