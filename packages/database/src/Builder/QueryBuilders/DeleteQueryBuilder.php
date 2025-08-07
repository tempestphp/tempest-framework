<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\DeleteStatement;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 * @use \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModel>
 */
final class DeleteQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods, TransformsQueryBuilder;

    private DeleteStatement $delete;

    private array $bindings = [];

    private ModelInspector $model;

    /**
     * @param class-string<TModel>|string|TModel $model
     */
    public function __construct(string|object $model)
    {
        $this->model = inspect($model);
        $this->delete = new DeleteStatement($this->model->getTableDefinition());
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

    private function getStatementForWhere(): HasWhereStatements
    {
        return $this->delete;
    }

    private function getModel(): ModelInspector
    {
        return $this->model;
    }
}
