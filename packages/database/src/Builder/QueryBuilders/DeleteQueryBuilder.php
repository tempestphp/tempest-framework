<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\DeleteStatement;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;

/**
 * @template TModelClass of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModelClass>
 * @uses \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModelClass>
 */
final class DeleteQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods;

    private DeleteStatement $delete;

    private array $bindings = [];

    private ModelInspector $model;

    /**
     * @param class-string<TModelClass>|string|TModelClass $model
     */
    public function __construct(string|object $model)
    {
        $this->model = inspect($model);
        $this->delete = new DeleteStatement($this->model->getTableDefinition());
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

    public function toSql(): ImmutableString
    {
        return $this->build()->toSql();
    }

    public function toRawSql(): ImmutableString
    {
        return $this->build()->toRawSql();
    }

    public function build(mixed ...$bindings): Query
    {
        if ($this->model->isObjectModel() && is_object($this->model->instance) && $this->model->hasPrimaryKey()) {
            $primaryKeyValue = $this->model->getPrimaryKeyValue();

            if ($primaryKeyValue !== null) {
                $this->where($this->model->getPrimaryKey(), $primaryKeyValue->value);
            }
        }

        return new Query($this->delete, [...$this->bindings, ...$bindings])->onDatabase($this->onDatabase);
    }

    private function getStatementForWhere(): HasWhereStatements
    {
        return $this->delete;
    }

    private function getModel(): ModelInspector
    {
        return $this->model;
    }
}
