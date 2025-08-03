<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\QueryStatements\WhereGroupStatement;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final class WhereGroupBuilder
{
    use HasConvenientWhereMethods;

    /** @var array<WhereStatement|WhereGroupStatement> */
    private array $conditions = [];

    /** @var array<mixed> */
    private array $bindings = [];

    public function __construct(
        private readonly ModelInspector $model,
    ) {}

    /**
     * Adds a `WHERE` condition to the group.
     */
    public function where(string $field, mixed $value = null, string|WhereOperator $operator = WhereOperator::EQUALS): self
    {
        return $this->andWhere($field, $value, WhereOperator::fromOperator($operator));
    }

    /**
     * Adds a `WHERE` condition to the group.
     */
    public function andWhere(string $field, mixed $value = null, WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        if ($this->conditions !== []) {
            $condition['sql'] = "AND {$condition['sql']}";
        }

        $this->conditions[] = new WhereStatement($condition['sql']);
        $this->bindings = [...$this->bindings, ...$condition['bindings']];

        return $this;
    }

    /**
     * Adds a `OR WHERE` condition to the group.
     */
    public function orWhere(string $field, mixed $value = null, string|WhereOperator $operator = WhereOperator::EQUALS): self
    {
        $operator = WhereOperator::fromOperator($operator);
        $fieldDefinition = $this->model->getFieldDefinition($field);
        $condition = $this->buildCondition((string) $fieldDefinition, $operator, $value);

        if ($this->conditions !== []) {
            $condition['sql'] = "OR {$condition['sql']}";
        }

        $this->conditions[] = new WhereStatement($condition['sql']);
        $this->bindings = [...$this->bindings, ...$condition['bindings']];

        return $this;
    }

    /**
     * Adds a raw SQL `WHERE` condition to the group.
     */
    public function whereRaw(string $rawCondition, mixed ...$bindings): self
    {
        if ($this->conditions !== [] && ! str($rawCondition)->trim()->startsWith(['AND', 'OR'])) {
            $rawCondition = "AND {$rawCondition}";
        }

        $this->conditions[] = new WhereStatement($rawCondition);
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    /**
     * Adds a raw SQL `AND WHERE` condition to the group.
     */
    public function andWhereRaw(string $rawCondition, mixed ...$bindings): self
    {
        if ($this->conditions !== []) {
            $rawCondition = "AND {$rawCondition}";
        }

        $this->conditions[] = new WhereStatement($rawCondition);
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    /**
     * Adds a raw SQL `OR WHERE` condition to the group.
     */
    public function orWhereRaw(string $rawCondition, mixed ...$bindings): self
    {
        if ($this->conditions !== []) {
            $rawCondition = "OR {$rawCondition}";
        }

        $this->conditions[] = new WhereStatement($rawCondition);
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    /**
     * Adds another nested where statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder):void $callback
     * @param 'AND'|'OR' $operator
     */
    public function whereGroup(Closure $callback, string $operator = 'AND'): self
    {
        $groupBuilder = new WhereGroupBuilder($this->model);
        $callback($groupBuilder);

        $group = $groupBuilder->build();

        if (! $group->conditions->isEmpty()) {
            if ($this->conditions !== []) {
                $this->conditions[] = new WhereStatement($operator);
            }

            $this->conditions[] = $group;
            $this->bindings = [...$this->bindings, ...$groupBuilder->getBindings()];
        }

        return $this;
    }

    /**
     * Adds another nested `AND WHERE` statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder):void $callback
     */
    public function andWhereGroup(Closure $callback): self
    {
        return $this->whereGroup($callback, 'AND');
    }

    /**
     * Adds another nested `OR WHERE` statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder):void $callback
     */
    public function orWhereGroup(Closure $callback): self
    {
        return $this->whereGroup($callback, 'OR');
    }

    public function build(): WhereGroupStatement
    {
        return new WhereGroupStatement(
            conditions: arr($this->conditions),
        );
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
