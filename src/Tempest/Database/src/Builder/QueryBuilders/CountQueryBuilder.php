<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\CountStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Support\Conditions\HasConditions;

/**
 * @template TModelClass of object
 */
final class CountQueryBuilder
{
    use HasConditions;

    /** @var class-string<TModelClass> $modelClass */
    private readonly string $modelClass;

    private ?ModelDefinition $modelDefinition;

    private CountStatement $count;

    private array $bindings = [];

    public function __construct(string|object $model, ?string $column = null)
    {
        $this->modelDefinition = ModelDefinition::tryFrom($model);
        $this->modelClass = is_object($model) ? $model::class : $model;

        $this->count = new CountStatement(
            table: $this->resolveTable($model),
            column: $column,
        );
    }

    public function execute(mixed ...$bindings): int
    {
        $key = "COUNT({$this->count->countArgument})";

        return $this->build()->fetchFirst(...$bindings)[$key];
    }

    /** @return self<TModelClass> */
    public function where(string $where, mixed ...$bindings): self
    {
        $this->count->where[] = new WhereStatement($where);

        $this->bind(...$bindings);

        return $this;
    }

    public function andWhere(string $where, mixed ...$bindings): self
    {
        return $this->where("AND {$where}", ...$bindings);
    }

    public function orWhere(string $where, mixed ...$bindings): self
    {
        return $this->where("OR {$where}", ...$bindings);
    }

    /** @return self<TModelClass> */
    public function whereField(string $field, mixed $value): self
    {
        $field = $this->modelDefinition->getFieldDefinition($field);

        return $this->where("{$field} = :{$field->name}", ...[$field->name => $value]);
    }

    /** @return self<TModelClass> */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    public function toSql(): string
    {
        return $this->build()->getSql();
    }

    public function build(array $bindings = []): Query
    {
        return new Query($this->count, [...$this->bindings, ...$bindings]);
    }

    private function clone(): self
    {
        return clone $this;
    }

    private function resolveTable(string|object $model): TableDefinition
    {
        if ($this->modelDefinition === null) {
            return new TableDefinition($model);
        }

        return $this->modelDefinition->getTableDefinition();
    }
}
