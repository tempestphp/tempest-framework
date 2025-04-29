<?php

namespace Tempest\Database {
    use ReflectionException;
    use Tempest\Database\Builder\ModelInspector;
    use Tempest\Database\Builder\QueryBuilders\QueryBuilder;

    function query(string|object $model): QueryBuilder
    {
        return new QueryBuilder($model);
    }

    function model(string|object $model): ModelInspector
    {
        return new ModelInspector($model);
    }
}
