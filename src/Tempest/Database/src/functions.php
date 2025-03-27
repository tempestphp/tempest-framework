<?php

namespace Tempest\Database {
    use Tempest\Database\Builder\QueryBuilders\QueryBuilder;

    function query(string|object $model): QueryBuilder
    {
        return new QueryBuilder($model);
    }
}
