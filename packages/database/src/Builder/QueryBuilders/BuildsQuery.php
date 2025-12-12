<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Query;

/**
 * @template TModel
 */
interface BuildsQuery
{
    /**
     * The current bindings for this query builder.
     *
     * @return array<mixed>
     */
    public array $bindings {
        get;
    }

    /**
     * The model inspector for this query builder.
     */
    public ModelInspector $model {
        get;
    }

    /**
     * Creates a {@see Query} instance with the specified optional bindings.
     *
     * ### Example
     * ```php
     * $builder->build(id: $id);
     * ```
     */
    public function build(mixed ...$bindings): Query;

    /**
     * Registers the specified bindings for this query.
     *
     * ### Example
     * ```php
     * $builder->bind(id: $id);
     * ```
     *
     * @return self<TModel>
     */
    public function bind(mixed ...$bindings): self;
}
