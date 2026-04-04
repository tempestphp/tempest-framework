<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

interface QueryScope
{
    public function apply(SupportsWhereStatements $builder): void;
}
