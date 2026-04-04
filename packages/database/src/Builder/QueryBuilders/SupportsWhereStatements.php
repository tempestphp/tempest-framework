<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\QueryStatements\WhereGroupStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Support\Arr\ImmutableArray;

/**
 * @template TModel
 */
interface SupportsWhereStatements
{
    /**
     * The current WHERE statements for this query builder.
     *
     * @var ImmutableArray<WhereStatement|WhereGroupStatement>
     */
    public ImmutableArray $wheres { get; }

    /**
     * Adds a WHERE condition to the query.
     *
     * @return self<TModel>
     */
    public function whereField(string $field, mixed $value, string|WhereOperator $operator = WhereOperator::EQUALS): self;

    /**
     * Adds an OR WHERE condition to the query.
     *
     * @return self<TModel>
     */
    public function orWhere(string $field, mixed $value, WhereOperator $operator = WhereOperator::EQUALS): self;

    /**
     * Adds a raw WHERE condition to the query.
     *
     * @return self<TModel>
     */
    public function whereRaw(string $statement, mixed ...$bindings): self;
}
