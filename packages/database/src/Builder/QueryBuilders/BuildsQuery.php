<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Query;

/**
 * @template TModelClass
 */
interface BuildsQuery
{
    public function build(mixed ...$bindings): Query;

    /** @return self<TModelClass> */
    public function bind(mixed ...$bindings): self;
}
