<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\str;

/**
 * @template TModelClass
 * @phpstan-require-implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery
 */
trait HasWhereQueryBuilderMethods
{
    abstract private function getModel(): ModelInspector;

    abstract private function getStatementForWhere(): HasWhereStatements;

    /** @return self<TModelClass> */
    public function where(string $where, mixed ...$bindings): self
    {
        if ($this->getStatementForWhere()->where->isNotEmpty() && ! str($where)->trim()->startsWith(['AND', 'OR'])) {
            return $this->andWhere($where, ...$bindings);
        }

        $this->getStatementForWhere()->where[] = new WhereStatement($where);

        $this->bind(...$bindings);

        return $this;
    }

    /** @return self<TModelClass> */
    public function andWhere(string $where, mixed ...$bindings): self
    {
        return $this->where("AND {$where}", ...$bindings);
    }

    /** @return self<TModelClass> */
    public function orWhere(string $where, mixed ...$bindings): self
    {
        return $this->where("OR {$where}", ...$bindings);
    }

    /** @return self<TModelClass> */
    public function whereField(string $field, mixed $value): self
    {
        $field = $this->getModel()->getFieldDefinition($field);

        return $this->where("{$field} = ?", $value);
    }
}
