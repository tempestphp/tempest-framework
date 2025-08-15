<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Query;

/**
 * @template TModel
 */
interface BuildsQuery
{
    public function build(mixed ...$bindings): Query;

    /** @return self<TModel> */
    public function bind(mixed ...$bindings): self;
}
