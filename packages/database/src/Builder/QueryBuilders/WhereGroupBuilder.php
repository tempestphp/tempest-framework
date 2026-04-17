<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Builder\WhereConnector;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\QueryStatements\WhereExistsStatement;
use Tempest\Database\QueryStatements\WhereGroupStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Str;

use function Tempest\Support\arr;
use function Tempest\Support\str;

/**
 * @template TModel
 * @uses \Tempest\Database\Builder\QueryBuilders\HasConvenientWhereMethods<TModel>
 */
final class WhereGroupBuilder
{
    use HasConvenientWhereMethods;

    /** @var array<WhereStatement|WhereGroupStatement|WhereExistsStatement> */
    private array $conditions = [];

    /** @var array<mixed> */
    private array $bindings = [];

    public function __construct(
        private readonly ModelInspector $model,
    ) {}

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
     * Adds a `WHERE` condition to the group.
     *
     * @return self<TModel>
     */
    public function whereField(string $field, mixed $value = null, string|WhereOperator $operator = WhereOperator::EQUALS): self
    {
        return $this->andWhere($field, $value, WhereOperator::fromOperator($operator));
    }

    /**
     * Adds a `WHERE` condition to the group.
     *
     * @return self<TModel>
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
     *
     * @return self<TModel>
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
     *
     * @return self<TModel>
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
     *
     * @return self<TModel>
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
     *
     * @return self<TModel>
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
     * Adds a `WHERE EXISTS` condition for a relation to the group.
     * When `$count` is 1 and `$operator` is `>=` (defaults), uses an `EXISTS` subquery.
     * Otherwise, uses a `COUNT(*)` subquery with the given operator and count.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return self<TModel>
     */
    public function whereHas(
        string $relation,
        ?Closure $callback = null,
        string|WhereOperator $operator = WhereOperator::GREATER_THAN_OR_EQUAL,
        int $count = 1,
    ): self {
        return $this->addHasCondition(
            relation: $relation,
            callback: $callback,
            operator: WhereOperator::fromOperator(value: $operator),
            count: $count,
            connector: WhereConnector::AND,
            negate: false,
        );
    }

    /**
     * Adds a `WHERE NOT EXISTS` condition for a relation to the group.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return self<TModel>
     */
    public function whereDoesntHave(
        string $relation,
        ?Closure $callback = null,
    ): self {
        return $this->addHasCondition(
            relation: $relation,
            callback: $callback,
            operator: WhereOperator::GREATER_THAN_OR_EQUAL,
            count: 1,
            connector: WhereConnector::AND,
            negate: true,
        );
    }

    /**
     * Adds an `OR WHERE EXISTS` condition for a relation to the group.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return self<TModel>
     */
    public function orWhereHas(
        string $relation,
        ?Closure $callback = null,
        string|WhereOperator $operator = WhereOperator::GREATER_THAN_OR_EQUAL,
        int $count = 1,
    ): self {
        return $this->addHasCondition(
            relation: $relation,
            callback: $callback,
            operator: WhereOperator::fromOperator(value: $operator),
            count: $count,
            connector: WhereConnector::OR,
            negate: false,
        );
    }

    /**
     * Adds an `OR WHERE NOT EXISTS` condition for a relation to the group.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return self<TModel>
     */
    public function orWhereDoesntHave(
        string $relation,
        ?Closure $callback = null,
    ): self {
        return $this->addHasCondition(
            relation: $relation,
            callback: $callback,
            operator: WhereOperator::GREATER_THAN_OR_EQUAL,
            count: 1,
            connector: WhereConnector::OR,
            negate: true,
        );
    }

    /**
     * @return self<TModel>
     */
    private function addHasCondition(
        string $relation,
        ?Closure $callback,
        WhereOperator $operator,
        int $count,
        WhereConnector $connector,
        bool $negate,
    ): self {
        $parts = str(string: $relation)->explode(
            separator: '.',
            limit: 2,
        );
        $relationName = (string) $parts[0];
        $nestedPath = isset($parts[1])
            ? (string) $parts[1]
            : null;

        $existsStatement = $this->model
            ->getRelation(name: $relationName)
            ->getExistsStatement();

        $useCount = ! $negate && ($count !== 1 || $operator !== WhereOperator::GREATER_THAN_OR_EQUAL);

        /** @var ImmutableArray<WhereStatement|WhereGroupStatement|WhereExistsStatement> $innerWheres */
        $innerWheres = arr();
        /** @var ImmutableArray<string|int|float|bool|null> $innerBindings */
        $innerBindings = arr();

        if ($nestedPath !== null) {
            $innerBuilder = new SelectQueryBuilder(model: $existsStatement->relatedModelName);
            $innerBuilder->whereHas(
                relation: $nestedPath,
                callback: $callback,
            );

            $innerWheres = $innerBuilder->wheres;
            $innerBindings = arr(input: $innerBuilder->bindings);
        } elseif ($callback instanceof Closure) {
            $innerBuilder = new SelectQueryBuilder(model: $existsStatement->relatedModelName);
            $callback($innerBuilder);

            $innerWheres = $innerBuilder->wheres;
            $innerBindings = arr(input: $innerBuilder->bindings);
        }

        $whereExists = new WhereExistsStatement(
            relatedTable: $existsStatement->relatedTable,
            relatedModelName: $existsStatement->relatedModelName,
            condition: $existsStatement->condition,
            joinStatement: $existsStatement->joinStatement,
            innerWheres: $innerWheres,
            negate: $negate,
            useCount: $useCount,
            operator: $operator,
            count: $count,
        );

        if ($this->conditions !== []) {
            $this->conditions[] = new WhereStatement($connector->value);
        }

        $this->conditions[] = $whereExists;
        $this->bindings = [...$this->bindings, ...$innerBindings->toArray()];

        return $this;
    }

    /**
     * Adds another nested where statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder<TModel>):void $callback
     * @param 'AND'|'OR' $operator
     *
     * @return self<TModel>
     */
    public function whereGroup(Closure $callback, string $operator = 'AND'): self
    {
        /** @var WhereGroupBuilder<TModel> $groupBuilder */
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
     * @param Closure(WhereGroupBuilder<TModel>):void $callback
     *
     * @return self<TModel>
     */
    public function andWhereGroup(Closure $callback): self
    {
        return $this->whereGroup($callback, 'AND');
    }

    /**
     * Adds another nested `OR WHERE` statement. The callback accepts a builder, which may be used to add more nested `WHERE` statements.
     *
     * @param Closure(WhereGroupBuilder<TModel>):void $callback
     *
     * @return self<TModel>
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
