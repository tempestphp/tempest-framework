<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\str;

/**
 * @template TModel of object
 * @phpstan-require-implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery
 * @phpstan-require-implements \Tempest\Database\Builder\QueryBuilders\SupportsWhereStatements
 * @use \Tempest\Database\Builder\QueryBuilders\HasConvenientWhereMethods<TModel>
 */
trait HasWhereQueryBuilderMethods
{
    use HasConvenientWhereMethods;

    /**
     * Adds a SQL `WHERE` condition to the query. If the `$statement` looks like raw SQL, the method will assume it is and call `whereRaw`. Otherwise, `whereField` will be called.
     *
     * **Example**
     * ```php
     * ->where('price > ?', $value); // calls `whereRaw`
     * ->where('price', $value); // calls `whereField`
     * ```
     * @return self<TModel>
     */
    public function where(string $statement, mixed ...$bindings): self
    {
        if ($this->looksLikeWhereRawStatement($statement, $bindings)) {
            return $this->whereRaw($statement, ...$bindings);
        }

        return $this->whereField($statement, value: $bindings[0], operator: $bindings[1] ?? WhereOperator::EQUALS);
    }

    /**
     * Adds a where condition to the query.
     *
     * @return self<TModel>
     */
    public function whereField(string $field, mixed $value, string|WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        if ($this->wheres->isNotEmpty()) {
            return $this->andWhere($field, $value, $operator);
        }

        $this->wheres[] = new WhereStatement($condition['sql']);
        $this->bind(...$condition['bindings']);

        return $this;
    }

    /**
     * Adds an `AND WHERE` condition to the query.
     *
     * @return self<TModel>
     */
    public function andWhere(string $field, mixed $value, WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        $this->wheres[] = new WhereStatement("AND {$condition['sql']}");
        $this->bind(...$condition['bindings']);

        return $this;
    }

    /**
     * Adds an `OR WHERE` condition to the query.
     *
     * @return self<TModel>
     */
    public function orWhere(string $field, mixed $value, WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        $this->wheres[] = new WhereStatement("OR {$condition['sql']}");
        $this->bind(...$condition['bindings']);

        return $this;
    }

    /**
     * Adds a raw SQL `WHERE` condition to the query.
     *
     * @return self<TModel>
     */
    public function whereRaw(string $statement, mixed ...$bindings): self
    {
        if ($this->wheres->isNotEmpty() && ! str($statement)->trim()->startsWith(['AND', 'OR'])) {
            return $this->andWhereRaw($statement, ...$bindings);
        }

        $this->wheres[] = new WhereStatement($statement);
        $this->bind(...$bindings);

        return $this;
    }

    /**
     * Adds a raw SQL `AND WHERE` condition to the query.
     *
     * @return self<TModel>
     */
    public function andWhereRaw(string $rawCondition, mixed ...$bindings): self
    {
        $this->wheres[] = new WhereStatement("AND {$rawCondition}");
        $this->bind(...$bindings);

        return $this;
    }

    /**
     * Adds a raw SQL `OR WHERE` condition to the query.
     *
     * @return self<TModel>
     */
    public function orWhereRaw(string $rawCondition, mixed ...$bindings): self
    {
        $this->wheres[] = new WhereStatement("OR {$rawCondition}");
        $this->bind(...$bindings);

        return $this;
    }

    /**
     * Adds a grouped where statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder<TModel>):void $callback
     * @return self<TModel>
     */
    public function whereGroup(Closure $callback): self
    {
        $groupBuilder = new WhereGroupBuilder($this->model);
        $callback($groupBuilder);
        $group = $groupBuilder->build();

        if (! $group->conditions->isEmpty()) {
            $this->wheres[] = $group;
            $this->bind(...$groupBuilder->getBindings());
        }

        return $this;
    }

    /**
     * Adds a grouped `AND WHERE` statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder<TModel>):void $callback
     * @return self<TModel>
     */
    public function andWhereGroup(Closure $callback): self
    {
        if ($this->wheres->isNotEmpty()) {
            $this->wheres[] = new WhereStatement('AND');
        }

        return $this->whereGroup($callback);
    }

    /**
     * Adds a grouped `OR WHERE` statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder<TModel>):void $callback
     * @return self<TModel>
     */
    public function orWhereGroup(Closure $callback): self
    {
        if ($this->wheres->isNotEmpty()) {
            $this->wheres[] = new WhereStatement('OR');
        }

        return $this->whereGroup($callback);
    }
}
