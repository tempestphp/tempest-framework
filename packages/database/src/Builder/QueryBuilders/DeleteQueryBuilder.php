<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\DeleteStatement;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Support\Conditions\HasConditions;

use function Tempest\Database\model;

/**
 * @template TModelClass of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModelClass>
 * @uses \Tempest\Database\Builder\QueryBuilders\IsQueryBuilderWithWhere<TModelClass>
 */
final class DeleteQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, IsQueryBuilderWithWhere;

    private DeleteStatement $delete;

    private array $bindings = [];

    public function __construct(
        /** @var class-string<TModelClass>|string $model */
        private readonly string|object $model,
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

    /** @return self<TModelClass> */
    public function allowAll(): self
    {
        $this->delete->allowAll = true;

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
        return new Query($this->delete, [...$this->bindings, ...$bindings])->onDatabase($this->onDatabase);
    }

    private function getStatementForWhere(): HasWhereStatements
    {
        return $this->delete;
    }
}
