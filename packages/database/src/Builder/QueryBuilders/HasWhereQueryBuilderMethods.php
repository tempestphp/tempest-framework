<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\QueryStatements\WhereExistsStatement;
use Tempest\Database\QueryStatements\WhereGroupStatement;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\str;

trait HasWhereQueryBuilderMethods
{
    use HasConvenientWhereMethods;
    use HasWhereRelationMethods;

    /**
     * @param QueryScope[] $scopes
     */
    public function applyScopes(array $scopes): self
    {
        foreach ($scopes as $scope) {
            $scope->apply(builder: $this);
        }

        return $this;
    }

    protected function appendWhere(WhereStatement|WhereGroupStatement|WhereExistsStatement $where): void
    {
        $this->wheres->offsetSet(null, $where);
    }

    /**
     * Adds a SQL `WHERE` condition to the query. If the `$statement` looks like raw SQL, the method will assume it is and call `whereRaw`. Otherwise, `whereField` will be called.
     *
     * **Example**
     * ```php
     * ->where('price > ?', $value); // calls `whereRaw`
     * ->where('price', $value); // calls `whereField`
     * ```
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
     */
    public function whereField(string $field, mixed $value, string|WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        if ($this->wheres->isNotEmpty()) {
            return $this->andWhere($field, $value, $operator);
        }

        $this->appendWhere(new WhereStatement($condition['sql']));
        $this->bind(...$condition['bindings']);

        return $this;
    }

    /**
     * Adds an `AND WHERE` condition to the query.
     */
    public function andWhere(string $field, mixed $value, WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        $this->appendWhere(new WhereStatement("AND {$condition['sql']}"));
        $this->bind(...$condition['bindings']);

        return $this;
    }

    /**
     * Adds an `OR WHERE` condition to the query.
     */
    public function orWhere(string $field, mixed $value, WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        $this->appendWhere(new WhereStatement("OR {$condition['sql']}"));
        $this->bind(...$condition['bindings']);

        return $this;
    }

    /**
     * Adds a raw SQL `WHERE` condition to the query.
     */
    public function whereRaw(string $statement, mixed ...$bindings): self
    {
        if ($this->wheres->isNotEmpty() && ! str($statement)->trim()->startsWith(['AND', 'OR'])) {
            return $this->andWhereRaw($statement, ...$bindings);
        }

        $this->appendWhere(new WhereStatement($statement));
        $this->bind(...$bindings);

        return $this;
    }

    /**
     * Adds a raw SQL `AND WHERE` condition to the query.
     */
    public function andWhereRaw(string $rawCondition, mixed ...$bindings): self
    {
        $this->appendWhere(new WhereStatement("AND {$rawCondition}"));
        $this->bind(...$bindings);

        return $this;
    }

    /**
     * Adds a raw SQL `OR WHERE` condition to the query.
     */
    public function orWhereRaw(string $rawCondition, mixed ...$bindings): self
    {
        $this->appendWhere(new WhereStatement("OR {$rawCondition}"));
        $this->bind(...$bindings);

        return $this;
    }

    /**
     * Adds a grouped where statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     */
    public function whereGroup(Closure $callback): self
    {
        /** @var WhereGroupBuilder<TModel> $groupBuilder */
        $groupBuilder = new WhereGroupBuilder($this->model);
        $callback($groupBuilder);
        $group = $groupBuilder->build();

        if (! $group->conditions->isEmpty()) {
            $this->appendWhere($group);
            $this->bind(...$groupBuilder->getBindings());
        }

        return $this;
    }

    /**
     * Adds a grouped `AND WHERE` statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     */
    public function andWhereGroup(Closure $callback): self
    {
        if ($this->wheres->isNotEmpty()) {
            $this->appendWhere(new WhereStatement('AND'));
        }

        return $this->whereGroup($callback);
    }

    /**
     * Adds a grouped `OR WHERE` statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     */
    public function orWhereGroup(Closure $callback): self
    {
        if ($this->wheres->isNotEmpty()) {
            $this->appendWhere(new WhereStatement('OR'));
        }

        return $this->whereGroup($callback);
    }
}
