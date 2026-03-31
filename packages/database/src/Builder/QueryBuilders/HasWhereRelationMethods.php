<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\WhereConnector;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\QueryStatements\WhereExistsStatement;
use Tempest\Database\QueryStatements\WhereGroupStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;
use function Tempest\Support\str;

trait HasWhereRelationMethods
{
    /**
     * Adds a `WHERE EXISTS` condition for a relation.
     * When `$count` is 1 and `$operator` is `>=` (defaults), uses an `EXISTS` subquery.
     * Otherwise, uses a `COUNT(*)` subquery with the given operator and count.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return static<TModel>
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
     * Adds a `WHERE NOT EXISTS` condition for a relation.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return static<TModel>
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
     * Adds an `OR WHERE EXISTS` condition for a relation.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return static<TModel>
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
     * Adds an `OR WHERE NOT EXISTS` condition for a relation.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return static<TModel>
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

        if ($this->wheres->isNotEmpty()) {
            $this->appendWhere(where: new WhereStatement(where: $connector->value));
        }

        $this->appendWhere(where: $whereExists);
        $this->bind(...$innerBindings->toArray());

        return $this;
    }
}
