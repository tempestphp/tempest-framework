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

use function Tempest\Database\model;

/**
 * @template TModelClass of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModelClass>
 * @uses \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModelClass>
 */
final class CountQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods, TapsQueryBuilder;

    private CountStatement $count;

    private array $bindings = [];

    private ModelInspector $model;

    public function __construct(
        /** @var class-string<TModelClass>|string|TModelClass $model */
        string|object $model,
        ?string $column = null,
    ) {
        $this->model = model($model);

        $this->count = new CountStatement(
            table: $this->model->getTableDefinition(),
            column: $column,
        );
    }

    public function execute(mixed ...$bindings): int
    {
        return $this->build()->fetchFirst(...$bindings)[$this->count->getKey()];
    }

    /** @return self<TModelClass> */
    public function distinct(): self
    {
        if ($this->count->column === null || $this->count->column === '*') {
            throw new CannotCountDistinctWithoutSpecifyingAColumn();
        }

        $this->count->distinct = true;

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

    private function clone(): self
    {
        return clone $this;
    }
}
