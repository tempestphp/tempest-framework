<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Query;

interface BuildsQuery
{
    public function build(mixed ...$bindings): Query;
}
