<?php

namespace Tempest\Database {
    use Tempest\Database\Builder\QueryBuilders\ModelQueryBuilder;

    function query(string|object $model): ModelQueryBuilder
    {
        return new ModelQueryBuilder($model);
    }
}
