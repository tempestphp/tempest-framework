<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\DeleteStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Database\UsesDatabase;
use Tempest\Support\Conditions\HasConditions;

use function Tempest\Database\model;

/**
 * @template TModelClass of object
 */
final class DeleteQueryBuilder implements BuildsQuery
{
    use HasConditions, UsesDatabase;

    private DeleteStatement $delete;

    private array $bindings = [];

    public function __construct(
        private string|object $model,
    ) {
        $table = ModelDefinition::tryFrom($this->model)?->getTableDefinition() ?? new TableDefinition($this->model);
        $this->delete = new DeleteStatement($table);

        if (model($this->model)->isObjectModel() && is_object($this->model)) {
            $this->where('`id` = :id', id: $this->model->id);
        }
    }

    public function execute(): void
    {
        $this->build()->execute();
    }

    public function allowAll(): self
    {
        $this->delete->allowAll = true;

        return $this;
    }

    public function where(string $where, mixed ...$bindings): self
    {
        $this->delete->where[] = new WhereStatement($where);

        $this->bind(...$bindings);

        return $this;
    }

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
        return new Query($this->delete, [...$this->bindings, ...$bindings])->useDatabase($this->useDatabase);
    }
}
