<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\CannotCountDistinctWithoutSpecifyingAColumn;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\CountStatement;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;

/**
 * @template T of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<T>
 * @uses \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<T>
 */
final class CountQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods;

    private CountStatement $count;

    private array $bindings = [];

    private ModelInspector $model;

    /**
     * @param class-string<T>|string|T $model
     */
    public function __construct(string|object $model, ?string $column = null)
    {
        $this->model = inspect($model);

        $this->count = new CountStatement(
            table: $this->model->getTableDefinition(),
            column: $column,
        );
    }

    /**
     * Executes the count query and returns the number of matching records.
     */
    public function execute(mixed ...$bindings): int
    {
        return $this->build()->fetchFirst(...$bindings)[$this->count->getKey()];
    }

    /**
     * Modifies the count query to only count distinct values in the specified column.
     *
     * @return self<T>
     */
    public function distinct(): self
    {
        if ($this->count->column === null || $this->count->column === '*') {
            throw new CannotCountDistinctWithoutSpecifyingAColumn();
        }

        $this->count->distinct = true;

        return $this;
    }

    /**
     * Binds the provided values to the query, allowing for parameterized queries.
     *
     * @return self<T>
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
        return new Query($this->count, [...$this->bindings, ...$bindings])->onDatabase($this->onDatabase);
    }

    private function getStatementForWhere(): HasWhereStatements
    {
        return $this->count;
    }

    private function getModel(): ModelInspector
    {
        return $this->model;
    }
}
