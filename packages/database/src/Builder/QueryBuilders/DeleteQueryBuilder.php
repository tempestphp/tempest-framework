<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\DeleteStatement;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 * @implements \Tempest\Database\Builder\QueryBuilders\SupportsWhereStatements<TModel>
 */
final class DeleteQueryBuilder implements BuildsQuery, SupportsWhereStatements
{
    use HasConditions;
    use OnDatabase;
    /** @use HasWhereQueryBuilderMethods<TModel> */
    use HasWhereQueryBuilderMethods;
    use TransformsQueryBuilder;

    private DeleteStatement $delete;

    public array $bindings = [];

    public ModelInspector $model;

    public ImmutableArray $wheres {
        get => $this->delete->where;
    }

    /**
     * @param class-string<TModel>|string|TModel $model
     */
    public function __construct(string|object $model)
    {
        $this->model = inspect($model);
        $this->delete = new DeleteStatement($this->model->getTableDefinition());
    }

    /**
     * Creates an instance from another query builder, inheriting conditions and bindings.
     *
     * @template TSourceModel of object
     * @param (BuildsQuery<TSourceModel>&SupportsWhereStatements<TSourceModel>) $source
     * @return DeleteQueryBuilder<TSourceModel>
     */
    public static function fromQueryBuilder(BuildsQuery&SupportsWhereStatements $source): DeleteQueryBuilder
    {
        $builder = new self($source->model->getName());
        $builder->bind(...$source->bindings);

        foreach ($source->wheres as $where) {
            $builder->appendWhere($where);
        }

        /** @var DeleteQueryBuilder<TSourceModel> $builder */
        return $builder;
    }

    /**
     * Executes the delete query, removing matching records from the database.
     */
    public function execute(): void
    {
        $this->build()->execute();
    }

    /**
     * Allows the delete operation to proceed without WHERE conditions, deleting all records.
     *
     * @return self<TModel>
     */
    public function allowAll(): self
    {
        $this->delete->allowAll = true;

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
     * Compile the query to a SQL statement without the bindings.
     */
    public function compile(): ImmutableString
    {
        return $this->build()->compile();
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
        if ($this->model->isObjectModel() && is_object($this->model->instance) && $this->model->hasPrimaryKey()) {
            $primaryKeyValue = $this->model->getPrimaryKeyValue();

            if ($primaryKeyValue !== null) {
                $this->where($this->model->getPrimaryKey(), $primaryKeyValue->value);
            }
        }

        return new Query($this->delete, [...$this->bindings, ...$bindings])->onDatabase($this->onDatabase);
    }
}
